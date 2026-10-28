<?php

class CarritoModel {

    // Conexión a la base de datos utilizada por los métodos del modelo.
    private $conexion;

    // Recibir y almacenar la conexión a la base de datos.
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Obtener el identificador del carrito de un usuario.
    // Si todavía no existe, crear uno y devolver su identificador como entero.
    public function obtenerOCrearCarrito($idUsuario) {

        // Asegurar que el identificador del usuario se maneje como un entero.
        $idUsuario = (int) $idUsuario;

        // Buscar el carrito asociado al usuario.
        $consulta = "SELECT id_carrito FROM carritos WHERE id_usuario = ? LIMIT 1";

        // Preparar la consulta para ejecutarla con un parámetro.
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el identificador del usuario al parámetro de tipo entero.
        mysqli_stmt_bind_param($stmt, "i", $idUsuario);

        // Ejecutar la consulta preparada.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Si la ejecución falla, cerrar la consulta y devolver false.
        if (!$ejecutado) {
            mysqli_stmt_close($stmt);
            return false;
        }

        // Obtener el resultado y recuperar la fila encontrada, si existe.
        $resultado = mysqli_stmt_get_result($stmt);
        $carrito = mysqli_fetch_assoc($resultado);

        // Cerrar la consulta después de procesar el resultado.
        mysqli_stmt_close($stmt);

        // Si el usuario ya tiene un carrito, devolver su identificador como entero.
        if ($carrito) {
            return (int) $carrito['id_carrito'];
        }

        // Si no existe un carrito, crear uno asociado al usuario.
        $consulta = "INSERT INTO carritos (id_usuario) VALUES (?)";
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la inserción, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el identificador del usuario al parámetro de la inserción.
        mysqli_stmt_bind_param($stmt, "i", $idUsuario);

        // Ejecutar la inserción del nuevo carrito.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Si la inserción falla, cerrar la consulta y devolver false.
        if (!$ejecutado) {
            mysqli_stmt_close($stmt);
            return false;
        }

        // Obtener el identificador generado automáticamente para el nuevo carrito.
        $idCarrito = mysqli_insert_id($this->conexion);

        // Cerrar la consulta preparada.
        mysqli_stmt_close($stmt);

        // Devolver el identificador del carrito como entero.
        return (int) $idCarrito;
    }

    // Obtener los productos y sus detalles correspondientes al carrito de un usuario.
    public function obtenerDetallesCarrito($idUsuario) {

        // Obtener el carrito existente o crear uno si todavía no existe.
        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        // Si no se pudo obtener o crear el carrito, indicar un fallo.
        if ($idCarrito === false) {
            return false;
        }

        // Consultar los productos del carrito y calcular el subtotal de cada uno.
        // El alias imagen permite utilizar un nombre de campo uniforme en la respuesta.
        // El alias ptp representa el precio total por producto según su cantidad.
        $consulta = "SELECT p.id_producto, p.nombre, p.marca, p.informacion, p.foto AS imagen,
        p.precio, p.stock, dc.cantidad, (p.precio * dc.cantidad) AS ptp
            FROM detalles_carrito dc
            INNER JOIN productos p
                ON dc.id_producto = p.id_producto
            WHERE dc.id_carrito = ? AND p.activo = 1
            ORDER BY p.nombre ASC
        ";

        // Preparar la consulta que relaciona los detalles del carrito con los productos.
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el identificador del carrito al parámetro de la consulta.
        mysqli_stmt_bind_param($stmt, "i", $idCarrito);

        // Ejecutar la consulta.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Si la ejecución falla, cerrar la consulta y devolver false.
        if (!$ejecutado) {
            mysqli_stmt_close($stmt);
            return false;
        }

        // Obtener el conjunto de resultados de la consulta.
        $resultado = mysqli_stmt_get_result($stmt);

        // Inicializar el array que almacenará los productos encontrados.
        $detalles = [];

        // Recorrer las filas y agregar cada producto al array de detalles.
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $detalles[] = $fila;
        }

        // Cerrar la consulta preparada.
        mysqli_stmt_close($stmt);

        // Devolver un array con los datos de cada producto y su cantidad.
        // Si el carrito está vacío, se devuelve un array vacío.
        return $detalles;
    }

    // Agregar un producto al carrito o incrementar su cantidad si ya existe.
    public function agregarProducto($idUsuario, $idProducto, $cantidad = 1) {

        // 1. Convertir los datos recibidos a enteros.
        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;
        $cantidad = (int) $cantidad;

        // Rechazar identificadores o cantidades que no sean positivos.
        if ($idUsuario <= 0 || $idProducto <= 0 || $cantidad <= 0) {
            return false;
        }

        // 2. Obtener el identificador del carrito del usuario.
        // Si no existe, el método se encarga de crearlo.
        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        // Comprobar que se haya obtenido un identificador válido.
        if ($idCarrito === false || $idCarrito <= 0) {
            return false;
        }

        // 3. Consultar el stock disponible del producto solicitado.
        $consulta = "SELECT stock FROM productos WHERE id_producto = ?";
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el identificador del producto a la consulta.
        mysqli_stmt_bind_param($stmt, "i", $idProducto);

        // Ejecutar la consulta y comprobar si se realizó correctamente.
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        // Recuperar el registro del producto, si existe, y cerrar la consulta.
        $resultado = mysqli_stmt_get_result($stmt);
        $producto = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        // Si no se encontró el producto, no se puede agregar al carrito.
        if (!$producto) {
            return false;
        }

        // Obtener el stock del producto como entero.
        $stock = (int) $producto['stock'];

        // 4. Consultar si el producto ya está incluido en el carrito.
        $consulta = "SELECT cantidad FROM detalles_carrito WHERE id_carrito = ? AND id_producto = ?
        ";

        // Preparar la consulta que busca el detalle del producto.
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el carrito y el producto a los parámetros de la consulta.
        mysqli_stmt_bind_param($stmt, "ii", $idCarrito, $idProducto);

        // Ejecutar la consulta y comprobar si se realizó correctamente.
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }

        // Recuperar la cantidad existente, si el producto ya está en el carrito.
        $resultado = mysqli_stmt_get_result($stmt);
        $detalle = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        // Si el producto no estaba en el carrito, considerar que su cantidad actual es cero.
        $cantidadActual = $detalle ? (int) $detalle['cantidad'] : 0;

        // 5. Comprobar que la cantidad acumulada no supere el stock consultado.
        // Se suma la cantidad existente a la nueva cantidad solicitada.
        if (($cantidadActual + $cantidad) > $stock) {
            return false;
        }

        // 6. Insertar el producto si no existe en el carrito.
        // Si ya existe, incrementar su cantidad mediante ON DUPLICATE KEY UPDATE.
        // Este comportamiento requiere una clave única que identifique la combinación
        // de id_carrito e id_producto en la tabla detalles_carrito.
        $consulta = "INSERT INTO detalles_carrito (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE cantidad = cantidad + ?";

        // Preparar la consulta de inserción o actualización.
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar los parámetros:
        // carrito, producto, cantidad inicial y cantidad que se sumará si ya existe.
        mysqli_stmt_bind_param($stmt, "iiii", $idCarrito, $idProducto, $cantidad, $cantidad);

        // Ejecutar la operación y guardar su resultado.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Cerrar la consulta preparada.
        mysqli_stmt_close($stmt);

        // Devolver true si la ejecución fue exitosa o false si falló.
        return $ejecutado;
    }

    // Actualizar la cantidad de un producto que ya está en el carrito.
    public function actualizarCantidad($idUsuario, $idProducto, $cantidad) {

        // 1. Convertir los datos recibidos a enteros.
        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;
        $cantidad = (int) $cantidad;

        // Rechazar identificadores o cantidades que no sean positivos.
        if ($idUsuario <= 0 || $idProducto <= 0 || $cantidad <= 0) {
            return false;
        }

        // 2. Obtener el identificador del carrito del usuario.
        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        // Si no se pudo obtener o crear el carrito, indicar un fallo.
        if ($idCarrito === false) {
            return false;
        }

        // 3. Actualizar la cantidad del producto únicamente si no supera su stock.
        // La unión con productos permite comprobar el stock durante la actualización.
        // Los filtros identifican el carrito y el producto correspondientes.
        $consulta = " UPDATE detalles_carrito dc INNER JOIN productos p ON dc.id_producto = p.id_producto
        SET dc.cantidad = ? WHERE dc.id_carrito = ? AND dc.id_producto = ? AND ? <= p.stock
        ";

        // Preparar la consulta de actualización.
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar la nueva cantidad, el carrito, el producto y la cantidad
        // que se compara con el stock disponible.
        mysqli_stmt_bind_param($stmt, "iiii", $cantidad, $idCarrito, $idProducto, $cantidad);

        // Ejecutar la actualización.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Cerrar la consulta preparada.
        mysqli_stmt_close($stmt);

        // Devolver el resultado de la ejecución.
        return $ejecutado;
    }

    // Eliminar un producto específico del carrito de un usuario.
    public function eliminarProducto($idUsuario, $idProducto) {

        // Comprobar que ambos identificadores sean numéricos y positivos.
        if (!is_numeric($idUsuario) || !is_numeric($idProducto) || (int) $idUsuario <= 0 || (int) $idProducto <= 0) {
            return false;
        }

        // Convertir los identificadores validados a enteros.
        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;

        // Obtener el identificador del carrito asociado al usuario.
        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        // Si no se pudo obtener o crear el carrito, indicar un fallo.
        if ($idCarrito === false || $idCarrito === null) {
            return false;
        }

        // Eliminar el detalle que corresponda al carrito y al producto indicados.
        $consulta = "DELETE FROM detalles_carrito WHERE id_carrito = ? AND id_producto = ?
        ";

        // Preparar la consulta de eliminación.
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el identificador del carrito y el del producto.
        mysqli_stmt_bind_param($stmt, "ii", $idCarrito, $idProducto);

        // Ejecutar la eliminación.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Cerrar la consulta preparada.
        mysqli_stmt_close($stmt);

        // Devolver el resultado de la ejecución.
        return $ejecutado;
    }

    // Eliminar todos los productos del carrito de un usuario.
    public function vaciarCarrito($idUsuario) {

        // Convertir el identificador del usuario a entero.
        $idUsuario = (int) $idUsuario;

        // Obtener el identificador del carrito asociado al usuario.
        $idCarrito = $this->obtenerOCrearCarrito($idUsuario);

        // Si no se pudo obtener o crear el carrito, indicar un fallo.
        if ($idCarrito === false || $idCarrito === null)  {
            return false;
        }

        // Eliminar todos los detalles asociados al carrito indicado.
        // El registro del carrito en sí no se elimina.
        $consulta = "DELETE FROM detalles_carrito WHERE id_carrito = ?";
        $stmt = mysqli_prepare($this->conexion, $consulta);

        // Si no se pudo preparar la consulta, indicar un fallo.
        if (!$stmt) {
            return false;
        }

        // Asociar el identificador del carrito a la consulta.
        mysqli_stmt_bind_param($stmt, "i", $idCarrito);

        // Ejecutar la eliminación de los detalles.
        $ejecutado = mysqli_stmt_execute($stmt);

        // Cerrar la consulta preparada.
        mysqli_stmt_close($stmt);

        // Devolver el resultado de la ejecución.
        return $ejecutado;
    }

}