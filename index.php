<?php

// La sesión debe abrirse ANTES de imprimir cualquier HTML. Si se llama
// dentro de una vista (al final del render, cuando ya se envió la página)
// PHP avisa: "Session cannot be started after headers have already been sent".
session_start();

require_once __DIR__ . "/config/conexion.php";
require_once __DIR__ . "/config/auth.php";
require_once __DIR__ . "/config/estrellas.php";

require_once __DIR__ . "/models/ProductoModel.php";
require_once __DIR__ . "/models/PedidoModel.php";
require_once __DIR__ . "/models/UsuarioModel.php";
require_once __DIR__ . "/models/CarritoModel.php";
require_once __DIR__ . "/models/ReseniaModel.php";

require_once __DIR__ . "/controllers/CarritoController.php";
require_once __DIR__ . "/controllers/ProductoController.php";
require_once __DIR__ . "/controllers/PedidoController.php";
require_once __DIR__ . "/controllers/UsuarioController.php";
require_once __DIR__ . "/controllers/ReseniaController.php";
require_once __DIR__ . "/controllers/AdminProductoController.php";
require_once __DIR__ . "/controllers/AdminUsuarioController.php";
require_once __DIR__ . "/controllers/AdminPedidoController.php";

/* Si el usuario de la sesión fue suspendido con la sesión ya iniciada, se le
   cierra en la siguiente página que abra. Se consulta la base en cada
   petición para que la suspensión tenga efecto sin esperar a que salga. */
if (!empty($_SESSION['id_usuario'])) {

    $usuarioSesionModel = new UsuarioModel($conn);
    $usuarioSesion = $usuarioSesionModel->obtenerPorId((int) $_SESSION['id_usuario']);

    if ($usuarioSesion) {

        if ((int) ($usuarioSesion['suspendida'] ?? 0) === 1) {
            cerrarSesion();
            header("Location: index.php?pagina=login&suspendida=1");
            exit;
        }

        // El rol también se refresca aquí: si un administrador lo cambió, el
        // usuario ve el panel con sus permisos nuevos sin volver a entrar.
        $_SESSION['rol'] = $usuarioSesion['rol'] ?? 'cliente';
    }
}

$paginaSolicitada = isset($_GET['pagina']) ? $_GET['pagina'] : 'home';

// El valor llega de la URL: solo se aceptan nombres de página con letras,
// números, guiones y guiones bajos. Así un valor raro nunca decide qué
// controlador se carga ni puede saltarse las comprobaciones de rol de abajo.
$paginaSolicitada = is_string($paginaSolicitada)
    ? preg_replace('/[^a-zA-Z0-9_-]/', '', $paginaSolicitada)
    : 'home';

if ($paginaSolicitada === '') {
    $paginaSolicitada = 'home';
}

// Las páginas del ABM solo están disponibles para administradores y
// operadores. El controlador lo vuelve a comprobar al construirse.
if (strpos($paginaSolicitada, 'admin_') === 0 && !esGestor()) {
    http_response_code(403);
    require __DIR__ . '/views/sin_permiso.php';
    exit;
}

// La gestión de usuarios es exclusiva del administrador.
if (strpos($paginaSolicitada, 'admin_usuario') === 0 && !esAdmin()) {    http_response_code(403);
    require __DIR__ . '/views/sin_permiso.php';
    exit;
}

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

    case 'resenias':
        // Guarda (POST) la reseña con estrellas y comentario del usuario
        // logueado y vuelve a la ficha del producto.
        $controlador = new ReseniaController($conn);
        $controlador->guardar();
        break;

    case 'carrito':
        require __DIR__ . '/views/paginaCarrito.php';
        break;

    case 'carrito-api':
        $controlador = new CarritoController($conn);
        $controlador->procesarSolicitud();
        break;

    case 'cuenta':
        // Datos del usuario logueado y foto de perfil.
        $controlador = new UsuarioController($conn);
        $controlador->cuenta();
        break;

    case 'panel':
        // Panel de gestión: solo administradores y operadores
        // (paginaPanel.php vuelve a comprobarlo al incluirse).
        require __DIR__ . '/views/paginaPanel.php';
        break;

    case 'checkout':
        // Datos de envío + método de pago, antes de guardar el pedido.
        // El carrito llega por POST desde el botón "Finalizar compra".
        $controlador = new PedidoController($conn);
        $controlador->mostrarCheckout();
        break;

    case 'confirmar-pedido':
        // Procesa el checkout: guarda pedido + detalle + pago (POST).
        $controlador = new PedidoController($conn);
        $controlador->confirmarPedido();
        break;

    case 'pedido':
        // Confirmación: lee el pedido ya guardado con ?pagina=pedido&id=N
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

    case 'admin_producto':
        // Lista de productos para administración
        $controlador = new AdminProductoController($conn);
        $controlador->listar();
        break;

    case 'admin_producto_crear':
        // Formulario para crear un nuevo producto
        $controlador = new AdminProductoController($conn);
        $controlador->crear();
        break;

    case 'admin_producto_editar':
        // Formulario para editar un producto existente
        $controlador = new AdminProductoController($conn);
        $controlador->editar();
        break;

    case 'admin_producto_estado':
        // Cambia el estado de un producto existente
        $controlador = new AdminProductoController($conn);
        $controlador->cambiarEstado();
        break;

    case 'usuarios':
    case 'admin_usuario':
        // Lista de usuarios para administración
        $controlador = new AdminUsuarioController($conn);
        $controlador->listar();
        break;

    case 'admin_usuario_editar':
        // Formulario para cambiar el rol o suspender una cuenta
        $controlador = new AdminUsuarioController($conn);
        $controlador->editar();
        break;

    case 'admin_usuario_rol':
        // Cambia el rol de un usuario desde el listado
        $controlador = new AdminUsuarioController($conn);
        $controlador->cambiarRol();
        break;

    case 'admin_usuario_estado':
        // Suspende o reactiva la cuenta de un usuario
        $controlador = new AdminUsuarioController($conn);
        $controlador->cambiarEstado();
        break;

    case 'pedidos':
    case 'admin_pedidos':
        // Lista de todos los pedidos (facturas) de cualquier usuario
        $controlador = new AdminPedidoController($conn);
        $controlador->listar();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Página no encontrada</h1>";
        break;
}
