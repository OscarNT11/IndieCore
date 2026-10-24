
<?php

class CarritoModel {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

   // Retornar (y crear si no existe) el Carrito (id en int)
    public function obtenerOCrearCarrito($idUsuario) {

        $idUsuario = (int) $idUsuario;

        $consulta = "SELECT id_carrito FROM carritos WHERE id_usuario = ? LIMIT 1";

        $stmt = mysqli_prepare($this->conexion, $consulta);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $idUsuario);
        $ejecutado = mysqli_stmt_execute($stmt);

        if (!$ejecutado) {
            mysqli_stmt_close($stmt);
            return false;
        }


        $resultado = mysqli_stmt_get_result($stmt);
        $carrito = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        // Si existe carrito, retorna el id en int
        if ($carrito) {
            return (int) $carrito['id_carrito'];
        }

        // Si no existe, lo crea y luego retorna el id en int
        $consulta = "INSERT INTO carritos (id_usuario) VALUES (?)";
        $stmt = mysqli_prepare($this->conexion, $consulta);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $idUsuario);
        $ejecutado = mysqli_stmt_execute($stmt);

        if (!$ejecutado) {
            mysqli_stmt_close($stmt);
            return false;
        }

        $idCarrito = mysqli_insert_id($this->conexion);

        mysqli_stmt_close($stmt);

        return (int) $idCarrito;
    }

    public function obtenerDetallesCarrito($idUsuario) {

        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        if ($idCarrito === false) {
            return false;
        }

        // Devuelve todos los atributos de producto, la cantidad por producto y el precio total por producto y su respectiva cantidad
        $consulta = "SELECT p.id_producto, p.nombre, p.marca, p.informacion, p.foto AS imagen,
        p.precio, p.stock, dc.cantidad, (p.precio * dc.cantidad) AS ptp
            FROM detalles_carrito dc
            INNER JOIN productos p
                ON dc.id_producto = p.id_producto
            WHERE dc.id_carrito = ?
            ORDER BY p.nombre ASC
        ";

        $stmt = mysqli_prepare($this->conexion, $consulta);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $idCarrito);
        $ejecutado = mysqli_stmt_execute($stmt);

        if (!$ejecutado) {
            mysqli_stmt_close($stmt);
            return false;
        }


        $resultado = mysqli_stmt_get_result($stmt);

        $detalles = [];

        while ($fila = mysqli_fetch_assoc($resultado)) {
            $detalles[] = $fila;
        }

        mysqli_stmt_close($stmt);

        // Devuelve un array de arrays de cada producto y su cantidad correspondiente
        return $detalles;
    }

    
public function agregarProducto($idUsuario, $idProducto, $cantidad = 1) {

    // 1. Validar los datos
    $idUsuario = (int) $idUsuario;
    $idProducto = (int) $idProducto;
    $cantidad = (int) $cantidad;

    if ($idUsuario <= 0 || $idProducto <= 0 || $cantidad <= 0) {
        return false;
    }

    // 2. Obtener el carrito
    $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

    if ($idCarrito === false || $idCarrito <= 0) {
        return false;
    }

    // 3. Consultar el stock del producto
    $consulta = "SELECT stock FROM productos WHERE id_producto = ?";
    $stmt = mysqli_prepare($this->conexion, $consulta);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $idProducto);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $producto = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    if (!$producto) {
        return false;
    }

    $stock = (int) $producto['stock'];

    // 4. Consultar si el producto ya está en el carrito
    $consulta = "SELECT cantidad FROM detalles_carrito WHERE id_carrito = ? AND id_producto = ?
    ";

    $stmt = mysqli_prepare($this->conexion, $consulta);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ii", $idCarrito, $idProducto);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return false;
    }

    $resultado = mysqli_stmt_get_result($stmt);
    $detalle = mysqli_fetch_assoc($resultado);
    mysqli_stmt_close($stmt);

    // Si no estaba no modifica
    $cantidadActual = $detalle ? (int) $detalle['cantidad'] : 0;

    // 5. Comprobar el stock acumulado
    if (($cantidadActual + $cantidad) > $stock) {
        return false;
    }

    // 6. Insertar el producto o incrementar su cantidad
    $consulta = "INSERT INTO detalles_carrito (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE cantidad = cantidad + ?";

    $stmt = mysqli_prepare($this->conexion, $consulta);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "iiii", $idCarrito, $idProducto, $cantidad, $cantidad);

    $ejecutado = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $ejecutado;
}


    
    public function actualizarCantidad($idUsuario, $idProducto, $cantidad) {
        // 1. Validaciones básicas
        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;
        $cantidad = (int) $cantidad;

        if ($idUsuario <= 0 || $idProducto <= 0 || $cantidad <= 0) {
            return false;
        }

        // 2. Obtener el carrito del usuario
        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);
        if ($idCarrito === false) {
            return false;
        }

        // 3. Actualizar la cantidad asegurando que no supere el stock actual del producto
        $consulta = " UPDATE detalles_carrito dc INNER JOIN productos p ON dc.id_producto = p.id_producto
        SET dc.cantidad = ? WHERE dc.id_carrito = ? AND dc.id_producto = ? AND ? <= p.stock
        ";

    $stmt = mysqli_prepare($this->conexion, $consulta);
    if (!$stmt) {
        return false;
    }

        mysqli_stmt_bind_param($stmt, "iiii", $cantidad, $idCarrito, $idProducto, $cantidad);
    
        $ejecutado = mysqli_stmt_execute($stmt);
    
        mysqli_stmt_close($stmt);

        return $ejecutado;
}

    public function eliminarProducto($idUsuario, $idProducto) {

        if (!is_numeric($idUsuario) || !is_numeric($idProducto) ||(int) $idUsuario <= 0 || (int) $idProducto <= 0) {
            return false;
        }

        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;

        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        if ($idCarrito === false || $idCarrito === null) {
            return false;
        }

        $consulta = "DELETE FROM detalles_carrito WHERE id_carrito = ? AND id_producto = ?
        ";

        $stmt = mysqli_prepare($this->conexion, $consulta);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "ii", $idCarrito, $idProducto);

        $ejecutado = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ejecutado;
    }
    
    public function vaciarCarrito($idUsuario) {

        $idUsuario = (int) $idUsuario;

        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        if ($idCarrito === false || $idCarrito === null)  {
            return false;
        }

        $consulta = "DELETE FROM detalles_carrito WHERE id_carrito = ?";
        $stmt = mysqli_prepare($this->conexion, $consulta);

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $idCarrito);

        $ejecutado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $ejecutado;
    }

}
