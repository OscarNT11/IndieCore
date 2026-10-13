<?php

// La sesión debe abrirse ANTES de imprimir cualquier HTML. Si se llama
// dentro de una vista (al final del render, cuando ya se envió la página)
// PHP avisa: "Session cannot be started after headers have already been sent".
session_start();

require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/config/auth.php";

require_once __DIR__ . "/models/ProductoModel.php";
require_once __DIR__ . "/models/PedidoModel.php";
require_once __DIR__ . "/models/UsuarioModel.php";
require_once __DIR__ . "/controllers/ProductoController.php";
require_once __DIR__ . "/controllers/PedidoController.php";
require_once __DIR__ . "/controllers/UsuarioController.php";

$paginaSolicitada = isset($_GET['pagina']) ? $_GET['pagina'] : 'home';

switch ($paginaSolicitada) {

    case 'home':
        // Página de inicio: muestra "Lo nuevo" con los productos de la BD
        $controlador = new ProductoController($conn);
        $controlador->mostrarInicio();
        break;

    case 'catalogo':
        $controlador = new ProductoController($conn);
        $controlador->mostrarCatalogo();
        break;

    case 'producto':
        // Detalle de un producto puntual: index.php?pagina=producto&id=3
        $controlador = new ProductoController($conn);
        $controlador->mostrarDetalle();
        break;

    case 'carrito':
        require __DIR__ . '/views/paginaCarrito.php';
        break;

    case 'cuenta':
        // Datos del usuario logueado y foto de perfil.
        $controlador = new UsuarioController($conn);
        $controlador->cuenta();
        break;

    case 'panel':
        // Panel de gestión: solo administradores y operadores.
        // requerirRol() corta con 403 y views/sin_permiso.php si el rol
        // no es el adecuado (o redirige al login si no hay sesión).
        if (!esGestor()) {
            http_response_code(403);
            require __DIR__ . '/views/sin_permiso.php';
            break;
        }

        require __DIR__ . '/views/paginaPanel.php';
        break;

    case 'confirmar-pedido':
        $controlador = new PedidoController($conn);
        $controlador->confirmarPedido();
        break;

    case 'pedido':
        // Confirmación renderizada tras guardar el pedido.
        $controlador = new PedidoController($conn);
        $controlador->mostrarConfirmacion();
        break;

    case 'registro':
        // Muestra el formulario y, si llega por POST, crea la cuenta.
        $controlador = new UsuarioController($conn);
        $controlador->registro();
        break;

    case 'login':
        // Muestra el formulario y, si llega por POST, abre la sesión.
        $controlador = new UsuarioController($conn);
        $controlador->login();
        break;

    case 'logout':
        $controlador = new UsuarioController($conn);
        $controlador->logout();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        break;
}
