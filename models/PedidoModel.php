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

    /* Un pedido por su id, sin importar quién lo hizo. Solo lo usa el panel
       de gestión (pedido visto por un administrador u operador). */
    public function obtenerPorIdAdmin($idPedido) {

        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id_pedido = ? LIMIT 1");

        if (!$stmt) {
            return null;
        }

        $idPedido = (int) $idPedido;

        $stmt->bind_param("i", $idPedido);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $pedido = $resultado ? $resultado->fetch_assoc() : null;

        $stmt->close();

        return $pedido ?: null;
    }

    /* Nombre y correo del usuario que hizo el pedido (o null si no existe).
       Es lo que la factura muestra como cliente cuando la abre el panel. */
    public function obtenerClientePorPedido($idPedido) {

        $stmt = $this->conn->prepare("
            SELECT u.id_usuario, u.nombre, u.email
            FROM pedidos p
            INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
            WHERE p.id_pedido = ? LIMIT 1");

        if (!$stmt) {
            return null;
        }

        $idPedido = (int) $idPedido;

        $stmt->bind_param("i", $idPedido);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $cliente = $resultado ? $resultado->fetch_assoc() : null;

        $stmt->close();

        return $cliente ?: null;
    }

    public function actualizarEstado($idPedido, $nuevoEstado) {
        $stmt = $this->conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $nuevoEstado, $idPedido);
        return $stmt->execute();
    }

    /* Historial de pedidos de un usuario para "Mi cuenta": la lista de sus
       pedidos (del más nuevo al más viejo), cada uno con su estado, el método
       de pago usado y sus líneas de detalle ya agrupadas.

       Se filtra SIEMPRE por id_usuario: un usuario solo ve sus pedidos.
       Se resuelve con dos consultas en vez de una por pedido (N+1): primero
       los pedidos, después todos sus detalles juntos. */
    public function listarHistorialPorUsuario($idUsuario) {

        $idUsuario = (int) $idUsuario;

        $stmt = $this->conn->prepare("
            SELECT p.*, pg.metodo_pago, pg.ultimos4, pg.resultado
            FROM pedidos p
            LEFT JOIN pagos pg ON pg.id_pedido = p.id_pedido
            WHERE p.id_usuario = ?
            ORDER BY p.fecha DESC, p.id_pedido DESC");

        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();

        $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (empty($pedidos)) {
            return array();
        }

        // Detalles de todos los pedidos del usuario en una sola consulta.
        $ids = array();
        foreach ($pedidos as $pedido) {
            $ids[] = (int) $pedido['id_pedido'];
        }

        $marcadores = implode(',', array_fill(0, count($ids), '?'));

        $stmtDetalles = $this->conn->prepare("
            SELECT dp.*, COALESCE(dp.nombre_producto, pr.nombre) AS nombre_producto
            FROM detalles_pedido dp
            INNER JOIN pedidos p ON p.id_pedido = dp.id_pedido
            LEFT JOIN productos pr ON pr.id_producto = dp.id_producto
            WHERE dp.id_pedido IN ($marcadores) AND p.id_usuario = ?");

        $tipos = str_repeat('i', count($ids)) . 'i';
        $parametros = array_merge($ids, array($idUsuario));

        $stmtDetalles->bind_param($tipos, ...$parametros);
        $stmtDetalles->execute();

        $detalles = $stmtDetalles->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtDetalles->close();

        // Cada detalle se cuelga de su pedido.
        $detallesPorPedido = array();

        foreach ($detalles as $detalle) {
            $detallesPorPedido[(int) $detalle['id_pedido']][] = $detalle;
        }

        foreach ($pedidos as $indice => $pedido) {
            $id = (int) $pedido['id_pedido'];

            $pedidos[$indice]['detalles'] = $detallesPorPedido[$id] ?? array();
        }

        return $pedidos;
    }

    /* ---------- Panel de administración de pedidos ---------- */

    /* TODOS los pedidos (de cualquier usuario) para el panel del gestor,
       del más nuevo al más viejo. Cada fila trae además el nombre y correo
       del cliente, el último pago registrado y cuántas unidades lleva.

       Se resuelve con UNA consulta: el detalle no se trae aquí (la factura
       lo carga al abrirse), solo el conteo de unidades con una subconsulta. */
    public function listarTodosAdmin() {

        $sql = "
            SELECT
                p.id_pedido,
                p.fecha,
                p.estado,
                p.precio_total,
                u.nombre AS cliente_nombre,
                u.email AS cliente_email,
                pg.metodo_pago,
                pg.ultimos4,
                (SELECT SUM(dp.cantidad) FROM detalles_pedido dp
                    WHERE dp.id_pedido = p.id_pedido) AS unidades
            FROM pedidos p
            INNER JOIN usuarios u ON u.id_usuario = p.id_usuario
            LEFT JOIN pagos pg ON pg.id_pago = (
                SELECT MAX(pg2.id_pago) FROM pagos pg2
                WHERE pg2.id_pedido = p.id_pedido
            )
            ORDER BY p.fecha DESC, p.id_pedido DESC";

        $consultaPreparada = mysqli_prepare($this->conn, $sql);

        if (!$consultaPreparada) {
            return array();
        }

        mysqli_stmt_execute($consultaPreparada);

        $resultado = mysqli_stmt_get_result($consultaPreparada);
        $pedidos = $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : array();

        mysqli_stmt_close($consultaPreparada);

        return $pedidos;
    }

    /* Resumen para las tarjetas del panel: cuántos pedidos hay y cuánto suman
       (sin contar los cancelados, que no representan una venta). */
    public function resumenAdmin() {

        $resultado = mysqli_query(
            $this->conn,
            "SELECT
                COUNT(*) AS total_pedidos,
                COALESCE(SUM(CASE WHEN estado <> 'cancelado' THEN precio_total ELSE 0 END), 0) AS total_facturado
             FROM pedidos"
        );

        $fila = $resultado ? mysqli_fetch_assoc($resultado) : null;

        return array(
            'total_pedidos'    => (int) ($fila['total_pedidos'] ?? 0),
            'total_facturado'  => (float) ($fila['total_facturado'] ?? 0),
        );
    }
}
