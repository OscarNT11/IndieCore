<?php

require_once __DIR__ . '/../models/CarritoModel.php';

class CarritoController {

    private $carritoModel;

    public function __construct($conexion) {
        $this->carritoModel = new CarritoModel($conexion);
    }

    public function procesarSolicitud() {

        header('Content-Type: application/json; charset=utf-8');

        // El carrito solo está disponible para usuarios autenticados.
        if (!usuarioLogueado()) {
            $this->responder([
                'ok' => false,
                'mensaje' => 'Debes iniciar sesión.'
            ]);
        }

        $idUsuario = (int) $_SESSION['id_usuario'];
        $accion = $_GET['accion'] ?? '';

        // Consultar el carrito.
        if ($accion === 'listar') {

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                $this->responder(['ok' => false]);
            }

            $carrito = $this->carritoModel
                ->obtenerDetallesCarrito($idUsuario);

            $this->responder([
                'ok' => $carrito !== false,
                'carrito' => $carrito
            ]);
        }

        // Las demás operaciones modifican el carrito y usan POST.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responder(['ok' => false]);
        }

        $idProducto = filter_var(
            $_POST['id_producto'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        $cantidad = filter_var(
            $_POST['cantidad'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        switch ($accion) {

            case 'agregar':

                if ($idProducto === false || $cantidad === false) {
                    $this->responder(['ok' => false]);
                }

                $resultado = $this->carritoModel->agregarProducto(
                    $idUsuario,
                    $idProducto,
                    $cantidad
                );
                break;

            case 'actualizar':

                if ($idProducto === false || $cantidad === false) {
                    $this->responder(['ok' => false]);
                }

                $resultado = $this->carritoModel->actualizarCantidad(
                    $idUsuario,
                    $idProducto,
                    $cantidad
                );
                break;

            case 'eliminar':

                if ($idProducto === false) {
                    $this->responder(['ok' => false]);
                }

                $resultado = $this->carritoModel->eliminarProducto(
                    $idUsuario,
                    $idProducto
                );
                break;

            case 'vaciar':

                $resultado = $this->carritoModel->vaciarCarrito(
                    $idUsuario
                );
                break;

            default:
                $this->responder(['ok' => false]);
        }

        if (!$resultado) {
            $this->responder([
                'ok' => false,
                'mensaje' => 'No se pudo actualizar el carrito.'
            ]);
        }

        // Devolver el carrito actualizado.
        $carrito = $this->carritoModel
            ->obtenerDetallesCarrito($idUsuario);

        $this->responder([
            'ok' => $carrito !== false,
            'carrito' => $carrito
        ]);
    }

    private function responder($datos) {
        echo json_encode(
            $datos,
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
        exit;
    }
}
