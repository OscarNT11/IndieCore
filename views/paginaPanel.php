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

require __DIR__ . '/header.php';
?>

<section class="panel">
    <h1 class="panel__titulo">Panel de gestión</h1>

    <p class="panel__saludo">
        Hola, <strong><?php echo escapar($usuarioPanel['nombre']); ?></strong>.
        <?php if (esAdmin()): ?>
            Tienes permisos de <strong>administrador</strong>.
        <?php else: ?>
            Tienes permisos de <strong>operador</strong>.
        <?php endif; ?>
    </p>

    <div class="panel__tarjetas">

        <a class="panel__tarjeta" href="index.php?pagina=catalogo">
            <h2>Productos</h2>
            <p>Ver y gestionar el catálogo de la tienda.</p>
        </a>

        <?php // Solo el administrador ve las opciones reservadas a su rol. ?>
        <?php if (esAdmin()): ?>
            <a class="panel__tarjeta" href="index.php?pagina=cuenta">
                <h2>Cuentas</h2>
                <p>Administración de usuarios y roles (desde phpMyAdmin).</p>
            </a>
        <?php endif; ?>

    </div>
</section>

<?php
require __DIR__ . '/modalLogin.php';
require __DIR__ . '/footer.php';
?>