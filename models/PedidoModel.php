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
       El estado queda como "pendiente" y la fecha la pone la BD. */
    public function crearPedido($precioTotal) {
        $consulta = "
            INSERT INTO pedidos (fecha, estado, precio_total)
            VALUES (NOW(), ?, ?)";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        $estadoInicial = "pendiente";
        $precioTotal = (float) $precioTotal;

        mysqli_stmt_bind_param($consultaPreparada, "sd", $estadoInicial, $precioTotal);

        if (!mysqli_stmt_execute($consultaPreparada)) {
            mysqli_stmt_close($consultaPreparada);
            return false;
        }

        $idPedido = mysqli_insert_id($this->conn);

        mysqli_stmt_close($consultaPreparada);

        return $idPedido;
    }

    /* Guarda una línea del detalle: qué producto, cuántas unidades y a qué
       precio unitario se compró. */
    public function agregarDetalleAlPedido($idPedido, $idProducto, $cantidad, $precioUnitario) {
        $consulta = "
            INSERT INTO detalles_pedido (cantidad, precio_unitario, id_pedido, id_producto)
            VALUES (?, ?, ?, ?)";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        $idPedido = (int) $idPedido;
        $idProducto = (int) $idProducto;
        $cantidad = (int) $cantidad;
        $precioUnitario = (float) $precioUnitario;

        mysqli_stmt_bind_param($consultaPreparada, "idid", $cantidad, $precioUnitario, $idPedido, $idProducto);

        $resultado = mysqli_stmt_execute($consultaPreparada);

        mysqli_stmt_close($consultaPreparada);

        return $resultado;
    }
}
