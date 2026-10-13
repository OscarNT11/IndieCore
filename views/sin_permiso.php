<?php
// ==========================
// Página que se muestra cuando un usuario logueado intenta entrar
// a una zona para la que su rol no tiene permiso.
// La incluye config/auth.php::requerirRol() con un código HTTP 403.
// ==========================
require_once __DIR__ . '/../config/auth.php';

require __DIR__ . '/header.php';
?>

<section class="sin-permiso">
    <h1>403 - Acceso denegado</h1>
    <p>Tu cuenta no tiene permiso para ver esta página.</p>

    <?php if (usuarioLogueado()): ?>
        <p>
            Has iniciado sesión como
            <strong><?php echo escapar(usuarioActual()['nombre']); ?></strong>
            (rol: <?php echo escapar(usuarioActualRol() ?? 'sin rol'); ?>).
        </p>
    <?php endif; ?>

    <p>
        <a class="boton boton--principal" href="index.php?pagina=home">Volver al inicio</a>
    </p>
</section>

<?php
require __DIR__ . '/modalLogin.php';
require __DIR__ . '/footer.php';
?>