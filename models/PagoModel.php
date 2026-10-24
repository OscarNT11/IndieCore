<?php

/* Registra el intento de pago de un pedido y actualiza el estado del pedido.
   El INSERT del pago y el UPDATE del estado del pedido van juntos en una
   sola transacción. */

class PagoModel {

    private $conn;

    /* Métodos de pago aceptados. Debe coincidir con el ENUM de la tabla. */
    public const METODOS_VALIDOS = array(
        'tarjeta_credito',
        'tarjeta_debito',
        'transferencia',
        'efectivo',
    );

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* Guarda el intento de pago y deja el pedido en su estado correcto:
       - tarjeta aprobada  -> 'pagado'
       - tarjeta rechazada -> 'rechazado'
       - transferencia o efectivo -> 'pendiente' (se cobra al entregar)

       $ultimos4 solo se guarda cuando el método usa tarjeta; en los demás
       casos se manda NULL. Devuelve true si se guardó todo, false si no. */
    
    public function registrarIntento($idPedido, $monto, $metodoPago, $ultimos4, $resultado) {

        $transaccionIniciada = false;

        try {
            mysqli_begin_transaction($this->conn);
            $transaccionIniciada = true;

            $this->registrarIntentoEnTransaccion(
                $idPedido,
                $monto,
                $metodoPago,
                $ultimos4,
                $resultado
            );

            mysqli_commit($this->conn);
            $transaccionIniciada = false;

            return true;

        } catch (Throwable $error) {

            if ($transaccionIniciada) {
                mysqli_rollback($this->conn);
            }

            return false;
        }
    }


    /* Registra el pago y actualiza el estado del pedido SIN iniciar,
    confirmar ni revertir una transacción.

    Permite que PedidoController incluya estas operaciones en la misma
    transacción que crea el pedido y descuenta el stock.

    Devuelve true si todo sale bien. Si una operación falla, lanza una
    excepción para que quien inició la transacción pueda revertirla. */
    public function registrarIntentoEnTransaccion(
        $idPedido,
        $monto,
        $metodoPago,
        $ultimos4,
        $resultado
    ) {

    $idPedido = (int) $idPedido;
    $monto = (float) $monto;

    if ($idPedido <= 0) {
        throw new Exception('El ID del pedido no es válido.');
    }

    // La columna resultado solo admite 'aprobado' o 'rechazado'.
    // Para efectivo y transferencia se registra como 'rechazado'
    // porque todavía no se cobró; el pedido queda 'pendiente'.
    $resultado = ($resultado === 'aprobado') ? 'aprobado' : 'rechazado';

    if (!in_array($metodoPago, self::METODOS_VALIDOS, true)) {
        throw new Exception('El método de pago no es válido.');
    }

    $pagaConTarjeta = (
        $metodoPago === 'tarjeta_credito' ||
        $metodoPago === 'tarjeta_debito'
    );

    if (!$pagaConTarjeta) {
        $ultimos4 = null;
    }

    if ($pagaConTarjeta) {
        $nuevoEstado = ($resultado === 'aprobado')
            ? 'pagado'
            : 'rechazado';
    } else {
        $resultado = 'rechazado';
        $nuevoEstado = 'pendiente';
    }

    // 1. Registrar el intento de pago.
    $stmt = mysqli_prepare(
        $this->conn,
        "INSERT INTO pagos
            (id_pedido, fecha_pago, monto, metodo_pago, ultimos4, resultado)
         VALUES (?, NOW(), ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('No se pudo preparar el registro del pago.');
    }

    mysqli_stmt_bind_param(
        $stmt,
        "idsss",
        $idPedido,
        $monto,
        $metodoPago,
        $ultimos4,
        $resultado
    );

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception('No se pudo guardar el pago.');
    }

    mysqli_stmt_close($stmt);

    // 2. Actualizar el estado del pedido.
    $stmtEstado = mysqli_prepare(
        $this->conn,
        "UPDATE pedidos SET estado = ? WHERE id_pedido = ?"
    );

    if (!$stmtEstado) {
        throw new Exception('No se pudo preparar la actualización del pedido.');
    }

    mysqli_stmt_bind_param(
        $stmtEstado,
        "si",
        $nuevoEstado,
        $idPedido
    );

    if (!mysqli_stmt_execute($stmtEstado)) {
        mysqli_stmt_close($stmtEstado);
        throw new Exception('No se pudo actualizar el estado del pedido.');
    }

    mysqli_stmt_close($stmtEstado);

    return true;
}


    /* Devuelve el último pago registrado de un pedido (o null si no hay). */
    public function obtenerPorPedido($idPedido) {

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT * FROM pagos WHERE id_pedido = ? ORDER BY fecha_pago DESC, id_pago DESC LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $idPedido = (int) $idPedido;

        mysqli_stmt_bind_param($stmt, "i", $idPedido);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        if (!$resultado) {
            mysqli_stmt_close($stmt);
            return null;
        }

        $pago = mysqli_fetch_assoc($resultado);

        mysqli_stmt_close($stmt);

        return $pago ? $pago : null;
    }

    /* Devuelve todos los pagos que quedaron aprobados. */
    public function obtenerAprobados() {
        $consulta = "SELECT * FROM pagos WHERE resultado = 'aprobado'";
        $resultado = mysqli_query($this->conn, $consulta);

        if (!$resultado) {
            return false;
        }

        $pagosAprobados = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
        mysqli_free_result($resultado);

        return $pagosAprobados;
    }
}