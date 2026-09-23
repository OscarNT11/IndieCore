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
                p.id_categoria,
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

    /* Productos de la MISMA categoría que el producto que se está viendo,
       excluyendo ese mismo producto. $limite acota cuántas tarjetas se muestran.

       El límite tiene que ser MAYOR que productos-por-vista (4) o el carrusel
       nunca se activa: carrusel.js solo pone flechas si la grilla trae más de
       4 tarjetas, y con un LIMIT 4 eso no pasa nunca. Se piden 12 para que
       las 6 de "Juegos" entren con margen y el carrusel tenga qué recorrer. */
    public function listarProductosRelacionados($idCategoria, $idProductoExcluido, $limite = 12) {

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
            WHERE p.id_categoria = ?
              AND p.id_producto <> ?
            ORDER BY p.nombre ASC
            LIMIT ?";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        $limite = (int) $limite;
        $idCategoria = (int) $idCategoria;
        $idProductoExcluido = (int) $idProductoExcluido;

        mysqli_stmt_bind_param($consultaPreparada, "iii", $idCategoria, $idProductoExcluido, $limite);

        mysqli_stmt_execute($consultaPreparada);

        $resultado = mysqli_stmt_get_result($consultaPreparada);

        $listaProductos = array();

        while ($fila = mysqli_fetch_assoc($resultado)) {
            $listaProductos[] = $fila;
        }

        return $listaProductos;
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
