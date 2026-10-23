<?php
// ==========================
// Panel de gestión: solo para usuarios con rol "administrador" u "operador".
// El enlace a esta página lo pinta el header según el rol de la sesión.
// La protección real está en index.php (requerirRol) y aquí se vuelve a
// comprobar por si la vista se incluye desde otro sitio.
// ==========================
require_once __DIR__ . '/../config/auth.php';

if (!esGestor()) {
    header("Location: index.php?pagina=home");
    exit;
}

$usuarioPanel = usuarioActual();
$claseBody = 'body-panel';

// Hoja propia del panel: barra lateral, formularios y listado.
$cssExtra = ['public/css/paginaPanel.css'];

require __DIR__ . '/header.php';
?>

<section class="panel-admin">

    <aside class="panel-admin__barra">
        <ul class="panel-admin__menu">
            <li>
                <a class="panel-admin__enlace" href="index.php?pagina=admin_producto">
                    <svg><use href="public/img/iconos.svg#icono-caja"></use></svg>
                    Productos
                </a>
            </li>
            <li>
                <a class="panel-admin__enlace" href="index.php?pagina=admin_pedidos">
                    <svg><use href="public/img/iconos.svg#icono-lista"></use></svg>
                    Pedidos
                </a>
            </li>

            <?php // Solo el administrador ve las opciones reservadas a su rol. ?>
            <?php if (esAdmin()): ?>
                <li>
                    <a class="panel-admin__enlace" href="index.php?pagina=usuarios">
                        <svg><use href="public/img/iconos.svg#icono-persona"></use></svg>
                        Usuarios
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </aside>

    <div class="panel-admin__contenido">
        <h1 class="panel__titulo">Panel de gestión</h1>
        <p class="panel__saludo">
            Hola, <?= escapar($usuarioPanel['nombre'] ?? '') ?>.
            Rol: <?= esAdmin() ? 'Administrador' : 'Operador' ?>.
        </p>

        <div class="panel__tarjetas">
            <a class="panel__tarjeta" href="index.php?pagina=admin_producto">
                <svg><use href="public/img/iconos.svg#icono-caja"></use></svg>
                <h2>Productos</h2>
            </a>

            <a class="panel__tarjeta" href="index.php?pagina=admin_pedidos">
                <svg><use href="public/img/iconos.svg#icono-lista"></use></svg>
                <h2>Pedidos</h2>
            </a>

            <?php if (esAdmin()): ?>
                <a class="panel__tarjeta" href="index.php?pagina=usuarios">
                    <svg><use href="public/img/iconos.svg#icono-persona"></use></svg>
                    <h2>Usuarios</h2>
                </a>
            <?php endif; ?>
        </div>
    </div>

</section>

<?php
require __DIR__ . '/footer_simple.php';
?>