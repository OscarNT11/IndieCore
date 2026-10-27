<?php

// Incluir el modelo encargado de gestionar las operaciones sobre el carrito.
require_once __DIR__ . '/../models/CarritoModel.php';

class CarritoController {

    // Modelo que permite consultar y modificar el carrito del usuario.
    private $carritoModel;

    // Recibir la conexión a la base de datos e inicializar el modelo.
    public function __construct($conexion) {
        $this->carritoModel = new CarritoModel($conexion);
    }

    // Procesar las solicitudes relacionadas con el carrito y devolver respuestas JSON.
    public function procesarSolicitud() {

        // Indicar que la respuesta del controlador estará en formato JSON y usará UTF-8.
        header('Content-Type: application/json; charset=utf-8');

        // El carrito solo está disponible para usuarios autenticados.
        if (!usuarioLogueado()) {
            $this->responder([
                'ok' => false,
                'mensaje' => 'Debes iniciar sesión.'
            ]);
        }

        // Obtener el identificador del usuario autenticado desde la sesión.
        $idUsuario = (int) $_SESSION['id_usuario'];

        // Obtener la acción solicitada mediante el parámetro GET.
        $accion = $_GET['accion'] ?? '';

        // Consultar el carrito.
        if ($accion === 'listar') {

            // La consulta del carrito solo admite solicitudes GET.
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->responder(['ok' => false]);
            }

            // Recuperar los productos y sus detalles asociados al carrito del usuario.
            $carrito = $this->carritoModel
                ->obtenerDetallesCarrito($idUsuario);

            // Devolver el resultado de la consulta.
            // La operación se considera exitosa si el modelo no devuelve false.
            $this->responder([
                'ok' => $carrito !== false,
                'carrito' => $carrito
            ]);
        }

        // Las demás operaciones modifican el carrito y usan POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false]);
        }

        // Obtener y validar el identificador del producto.
        // FILTER_VALIDATE_INT comprueba que sea un entero y min_range exige que sea mayor que cero.
        $idProducto = filter_var(
            $_POST['id_producto'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        // Obtener y validar la cantidad solicitada.
        // Solo se aceptan números enteros positivos.
        $cantidad = filter_var(
            $_POST['cantidad'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        // Ejecutar la operación solicitada según el valor del parámetro accion.
        switch ($accion) {

            case 'agregar':

                // Comprobar que el identificador y la cantidad sean enteros positivos válidos.
                if ($idProducto === false || $cantidad === false) {
                    $this->responder(['ok' => false]);
                }

                // Agregar el producto al carrito del usuario con la cantidad indicada.
                $resultado = $this->carritoModel->agregarProducto(
                    $idUsuario,
                    $idProducto,
                    $cantidad
                );
                break;

            case 'actualizar':

                // Verificar que el producto y la nueva cantidad sean válidos.
                if ($idProducto === false || $cantidad === false) {
                    $this->responder(['ok' => false]);
                }

                // Actualizar la cantidad de un producto que ya está en el carrito.
                $resultado = $this->carritoModel->actualizarCantidad(
                    $idUsuario,
                    $idProducto,
                    $cantidad
                );
                break;

            case 'eliminar':

                // Verificar que el identificador del producto sea válido.
                if ($idProducto === false) {
                    $this->responder(['ok' => false]);
                }

                // Eliminar el producto indicado del carrito del usuario.
                $resultado = $this->carritoModel->eliminarProducto(
                    $idUsuario,
                    $idProducto
                );
                break;

            case 'vaciar':

                // Eliminar todos los productos del carrito del usuario.
                $resultado = $this->carritoModel->vaciarCarrito(
                    $idUsuario
                );
                break;

            default:
                // Rechazar la solicitud si la acción no está contemplada.
                $this->responder(['ok' => false]);
        }

        // Comprobar si el modelo pudo completar la operación solicitada.
        if (!$resultado) {
            $this->responder([
                'ok' => false,
                'mensaje' => 'No se pudo actualizar el carrito.'
            ]);
        }

        // Devolver el carrito actualizado después de agregar, modificar o eliminar productos.
        $carrito = $this->carritoModel
            ->obtenerDetallesCarrito($idUsuario);

        // Indicar si la consulta posterior fue exitosa y enviar los datos del carrito.
        $this->responder([
            'ok' => $carrito !== false,
            'carrito' => $carrito
        ]);
    }

    // Enviar los datos recibidos como una respuesta JSON y finalizar la ejecución del script.
    private function responder($datos) {
        echo json_encode(
            $datos,
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
        exit;
    }
}