<?php

class PedidoModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* El pedido y su detalle se guardan dentro de una transacción: si algo
       falla, no queda un pedido a medias con su total pero sin líneas. */
    public function iniciarTransaccion() {
        mysqli_begin_transaction($this->conn);
    }

    public function confirmarTransaccion() {
        mysqli_commit($this->conn);
    }

    public function revertirTransaccion() {
        mysqli_rollback($this->conn);
    }

    /* Crea la fila del pedido y devuelve el id_pedido generado por la BD.
       El estado queda como "pendiente" (el pago lo cambia después a
       "pagado" / "rechazado") y la fecha la pone la BD.

       La tabla `pedidos` obliga a guardar los datos de envío, así que se
       reciben en $envio: nombre, direccion, ciudad, departamento, telefono.
       Devuelve el id del pedido o false si no se pudo guardar. */
    public function crearPedido($precioTotal, $envio, $idUsuario) {

        $estadoInicial = 'pendiente';
        $precioTotal = (float) $precioTotal;
        $idUsuario = (int) $idUsuario;

        $nombreEnvio = isset($envio['nombre']) ? (string) $envio['nombre'] : '';
        $direccionEnvio = isset($envio['direccion']) ? (string) $envio['direccion'] : '';
        $ciudadEnvio = isset($envio['ciudad']) ? (string) $envio['ciudad'] : '';
        $departamentoEnvio = isset($envio['departamento']) ? (string) $envio['departamento'] : '';
        $telefonoEnvio = isset($envio['telefono']) ? (string) $envio['telefono'] : '';

        $stmt = mysqli_prepare($this->conn, "
            INSERT INTO pedidos
                (fecha, estado, precio_total, envio_nombre, envio_direccion, envio_ciudad, envio_departamento, envio_telefono, id_usuario)
            VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            return false;
        }

        // "sdsssssi": estado (string), precio_total (double) y los cinco
        // datos de envío + id_usuario (strings e int).
        mysqli_stmt_bind_param(
            $stmt,
            "sdsssssi",
            $estadoInicial,
            $precioTotal,
            $nombreEnvio,
            $direccionEnvio,
            $ciudadEnvio,
            $departamentoEnvio,
            $telefonoEnvio,
            $idUsuario
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $idPedido = mysqli_insert_id($this->conn);

        mysqli_stmt_close($stmt);

        return $idPedido;
    }

    /* Guarda una línea del detalle: qué producto (y con qué nombre), cuántas
       unidades y a qué precio unitario se compró. El nombre se copia para que
       el pedido ya emitido no cambie si el producto se renombra después. */
    public function agregarDetalleAlPedido($idPedido, $idProducto, $cantidad, $precioUnitario, $nombreProducto = null) {
        $consulta = "
            INSERT INTO detalles_pedido (cantidad, precio_unitario, id_pedido, id_producto, nombre_producto)
            VALUES (?, ?, ?, ?, ?)";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        if (!$consultaPreparada) {
            return false;
        }

        $idPedido = (int) $idPedido;
        $idProducto = (int) $idProducto;
        $cantidad = (int) $cantidad;
        $precioUnitario = (float) $precioUnitario;
        $nombreProducto = $nombreProducto === null ? null : (string) $nombreProducto;

        mysqli_stmt_bind_param(
            $consultaPreparada,
            "idids",
            $cantidad,
            $precioUnitario,
            $idPedido,
            $idProducto,
            $nombreProducto
        );

        $resultado = mysqli_stmt_execute($consultaPreparada);

        mysqli_stmt_close($consultaPreparada);

        return $resultado;
    }

    public function obtenerPorId ($idPedido, $idUsuario) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $idPedido, $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function listarPedidosPorUsuario($idUsuario) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id_usuario = ? ORDER BY fecha DESC");
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /* Devuelve las líneas de un pedido. El nombre sale de la copia guardada
       en el detalle (nombre_producto); si es un pedido viejo y está en NULL,
       se cae al nombre actual del catálogo (por eso el LEFT JOIN). */
    public function obtenerDetallesPorPedido($idPedido) {
        $stmt = $this->conn->prepare("
            SELECT dp.*, COALESCE(dp.nombre_producto, p.nombre) AS nombre_producto
            FROM detalles_pedido dp
            LEFT JOIN productos p ON dp.id_producto = p.id_producto
            WHERE dp.id_pedido = ?");
        $stmt->bind_param("i", $idPedido);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function actualizarEstado($idPedido, $nuevoEstado) {
        $stmt = $this->conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $nuevoEstado, $idPedido);
        return $stmt->execute();
    }

}
