<?php

class ProductoModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function listarProductos($idCategoria = null) {
        // Se hace JOIN con categorias para obtener el nombre de la categoría,
        // y se devuelven alias que la vista consume directamente
        // (nombre, nombre_categoria, imagen).
        $consulta = "
            SELECT
                p.id_producto,
                p.nombre,
                p.marca,
                p.informacion,
                p.precio,
                p.stock,
                p.foto AS imagen,
                c.nombre AS nombre_categoria
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        ";

        if (!empty($idCategoria)) {
            $consulta .= " WHERE p.id_categoria = ?";
        }

        $consulta .= " ORDER BY p.nombre ASC";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        if (!empty($idCategoria)) {
            mysqli_stmt_bind_param($consultaPreparada, "i", $idCategoria);
        }

        mysqli_stmt_execute($consultaPreparada);
        $resultado = mysqli_stmt_get_result($consultaPreparada);

        $listaProductos = array();
        while ($row = mysqli_fetch_assoc($resultado)) {
            $listaProductos[] = $row;
        }
        return $listaProductos;
    }

    public function obtenerProductoPorId($idProducto) {
        $consulta = "
            SELECT
                p.id_producto,
                p.nombre,
                p.informacion,
                p.marca,
                p.precio,
                p.stock,
                p.foto AS imagen,
                c.nombre AS nombre_categoria
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            WHERE p.id_producto = ?";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        mysqli_stmt_bind_param($consultaPreparada, "i", $idProducto);

        mysqli_stmt_execute($consultaPreparada);

        $resultado = mysqli_stmt_get_result($consultaPreparada);

        return mysqli_fetch_assoc($resultado);
    }

    public function listarCategorias() {
        $consulta = "SELECT * FROM categorias ORDER BY nombre ASC";
        $resultado = mysqli_query($this->conn, $consulta);

        $listaCategorias = array();
        while ($row = mysqli_fetch_assoc($resultado)) {
            $listaCategorias[] = $row;
        }
        return $listaCategorias;
    }
}
?>
