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

    /* Nivel de stock calculado en SQL: 'agotado', 'bajo' o 'normal'. */
    private $sqlNivelStock = "CASE
                WHEN p.stock = 0 THEN 'agotado'
                WHEN p.stock <= p.stock_minimo THEN 'bajo'
                ELSE 'normal'
            END";

    /* Columnas comunes de los listados de administración. */
    private $columnasAdmin = "p.id_producto, p.nombre, p.marca, p.informacion,
            p.precio, p.stock, p.stock_minimo, p.activo, p.foto AS imagen,
            p.id_categoria, c.nombre AS nombre_categoria";

    /* Listado completo para el panel de administración, con el nivel de
       stock ya calculado y el nombre de la categoría. */
    public function listarTodosAdmin() {

        $sql = "SELECT $this->columnasAdmin,
                $this->sqlNivelStock AS nivel_stock
            FROM productos p
            INNER JOIN categorias c USING (id_categoria)
            ORDER BY p.nombre ASC";

        return $this->ejecutarConsulta($sql);
    }

    /* Un producto con los mismos datos que usa el listado de administración. */
    public function obtenerProductoAdmin($idProducto) {

        $sql = "SELECT $this->columnasAdmin,
                $this->sqlNivelStock AS nivel_stock
            FROM productos p
            INNER JOIN categorias c USING (id_categoria)
            WHERE p.id_producto = ?";

        $stmt = mysqli_prepare($this->conn, $sql);

        if (!$stmt) {
            return null;
        }

        $idProducto = (int) $idProducto;

        mysqli_stmt_bind_param($stmt, "i", $idProducto);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $producto = $resultado ? mysqli_fetch_assoc($resultado) : null;

        mysqli_stmt_close($stmt);

        return $producto ?: null;
    }

    /* Productos activos cuyo stock llegó al mínimo o lo pasó. */
    public function listarStockBajo() {

        $sql = "SELECT $this->columnasAdmin,
                $this->sqlNivelStock AS nivel_stock
            FROM productos p
            INNER JOIN categorias c USING (id_categoria)
            WHERE p.stock <= p.stock_minimo AND p.activo = 1
            ORDER BY p.nombre ASC";

        return $this->ejecutarConsulta($sql);
    }

    /* Alta de un producto. Devuelve el id creado o false si hubo error. */
    public function crear($datos) {

        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO productos (nombre, marca, informacion, precio, stock, stock_minimo, activo, foto, id_categoria)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $activo = (int) $datos['activo'];
        $precio = (float) $datos['precio'];
        $stock = (int) $datos['stock'];
        $stockMinimo = (int) $datos['stock_minimo'];
        $idCategoria = (int) $datos['id_categoria'];

        // Tipos en el orden de las columnas del INSERT:
        // nombre(s) marca(s) informacion(s) precio(d) stock(i) stock_minimo(i)
        // activo(i) foto(s) id_categoria(i)
        mysqli_stmt_bind_param(
            $stmt,
            "sssdiiisi",
            $datos['nombre'],
            $datos['marca'],
            $datos['informacion'],
            $precio,
            $stock,
            $stockMinimo,
            $activo,
            $datos['foto'],
            $idCategoria
        );

        $ejecutado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $ejecutado ? mysqli_insert_id($this->conn) : false;
    }

    /* Modificación de un producto existente. Devuelve true si se modificó. */
    public function modificar($datos) {

        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE productos SET nombre = ?, marca = ?, informacion = ?, precio = ?, stock = ?,
                    stock_minimo = ?, activo = ?, foto = ?, id_categoria = ?
             WHERE id_producto = ?"
        );

        if (!$stmt) {
            return false;
        }

        $activo = (int) $datos['activo'];
        $precio = (float) $datos['precio'];
        $stock = (int) $datos['stock'];
        $stockMinimo = (int) $datos['stock_minimo'];
        $idCategoria = (int) $datos['id_categoria'];
        $idProducto = (int) $datos['id_producto'];

        // mismo orden que el INSERT, con id_producto(i) al final.
        mysqli_stmt_bind_param(
            $stmt,
            "sssdiiisii",
            $datos['nombre'],
            $datos['marca'],
            $datos['informacion'],
            $precio,
            $stock,
            $stockMinimo,
            $activo,
            $datos['foto'],
            $idCategoria,
            $idProducto
        );

        $ejecutado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $ejecutado;
    }

    /* Baja lógica ($activo = 0) o reactivación ($activo = 1). */
    public function cambiarEstado($idProducto, $activo) {

        $stmt = mysqli_prepare($this->conn, "UPDATE productos SET activo = ? WHERE id_producto = ?");

        if (!$stmt) {
            return false;
        }

        $idProducto = (int) $idProducto;
        $activo = $activo ? 1 : 0;

        mysqli_stmt_bind_param($stmt, "ii", $activo, $idProducto);

        $ejecutado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $ejecutado;
    }

    /* Ejecuta una consulta de solo lectura y devuelve todas sus filas. */
    private function ejecutarConsulta($sql) {

        $resultado = mysqli_query($this->conn, $sql);

        if (!$resultado) {
            return array();
        }

        $filas = mysqli_fetch_all($resultado, MYSQLI_ASSOC);

        mysqli_free_result($resultado);

        return $filas;
    }
}
?>
