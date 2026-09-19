<?php

require_once __DIR__ . "/config/conexion.php";

require_once __DIR__ . "/models/ProductoModel.php";
require_once __DIR__ . "/models/PedidoModel.php";
require_once __DIR__ . "/controllers/ProductoController.php";
require_once __DIR__ . "/controllers/PedidoController.php";

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

    case 'confirmar-pedido':
        require __DIR__ . '/controllers/PedidoController.php';
        break;

    case 'registro':
        require __DIR__ . '/views/registro.php';
        break;

    case 'login':
        require __DIR__ . '/views/login.php';
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        break;
}
