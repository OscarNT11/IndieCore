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

    /* Busca productos por texto libre. Compara el término contra el nombre,
       la marca, la descripción (informacion) y el nombre de la categoría.
       Devuelve las filas con los mismos alias que usa la vista del listado. */
    public function buscarProductos($termino) {

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
            WHERE p.nombre       LIKE ?
               OR p.marca        LIKE ?
               OR p.informacion  LIKE ?
               OR c.nombre       LIKE ?
            ORDER BY p.nombre ASC";

        $consultaPreparada = mysqli_prepare($this->conn, $consulta);

        // El % va alrededor del término para que encuentre coincidencias
        // en cualquier parte del texto (no solo al principio).
        $patron = '%' . $termino . '%';

        mysqli_stmt_bind_param($consultaPreparada, "ssss", $patron, $patron, $patron, $patron);

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

    /* Descuenta del stock las unidades compradas de un producto.

       El UPDATE lleva la condición `stock >= ?`, así nunca queda stock
       negativo: si otro usuario se llevó las últimas unidades entre el
       recálculo del carrito y este momento, la consulta no modifica nada
       y se devuelve false para que el pedido se cancele.

       Se llama dentro de la transacción del pedido. */
    public function descontarStock($idProducto, $cantidad) {

        $idProducto = (int) $idProducto;
        $cantidad = (int) $cantidad;

        if ($idProducto <= 0 || $cantidad <= 0) {
            return false;
        }

        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE productos SET stock = stock - ? WHERE id_producto = ? AND stock >= ?"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "iii", $cantidad, $idProducto, $cantidad);

        $ejecutado = mysqli_stmt_execute($stmt);

        // affected_rows = 0 significa que no se actualizó ninguna fila
        // (el producto no existe o no quedaba stock suficiente).
        $seDesconto = $ejecutado && mysqli_stmt_affected_rows($stmt) === 1;

        mysqli_stmt_close($stmt);

        return $seDesconto;
    }
}
?>
