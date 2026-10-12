<?php
// CSS propio de esta vista. DEBE declararse antes de incluir el header,
// que es quien imprime los <link> a las hojas de estilo.
$cssExtra = ['public/css/paginaCuenta.css'];

require __DIR__ . '/header.php';

// $usuario lo rellena UsuarioController::cuenta() con los datos frescos
// de la BD (nombre, email, foto).
$fotoActual = !empty($usuario['foto'])
    ? $usuario['foto']
    : null;
?>

<section class="cuenta">

    <h1 class="cuenta__titulo">Mi cuenta</h1>

    <!-- ==========================
         AVISOS: éxito de guardado o lista de errores
         ========================== -->

    <?php if (!empty($mensajeOk)): ?>
        <div class="cuenta__aviso cuenta__aviso--ok" role="status">
            <?php echo escapar($mensajeOk); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="cuenta__aviso cuenta__aviso--error" role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo escapar($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- ==========================
         PANEL: foto a la izquierda, datos a la derecha
         enctype="multipart/form-data" es OBLIGATORIO para subir la foto.
         ========================== -->

    <form
        class="cuenta__panel"
        action="index.php?pagina=cuenta"
        method="POST"
        enctype="multipart/form-data">

        <!-- ---------- Columna izquierda: foto de perfil ---------- -->
        <div class="cuenta__foto-columna">

            <div class="cuenta__foto-marco">
                <?php if ($fotoActual): ?>
                    <img
                        class="cuenta__foto"
                        src="<?php echo escapar($fotoActual); ?>?v=<?php echo filemtime(__DIR__ . '/../' . $fotoActual); ?>"
                        alt="Foto de perfil de <?php echo escapar($usuario['nombre']); ?>">
                <?php else: ?>
                    <svg class="cuenta__foto-icono" aria-hidden="true">
                        <use href="public/img/iconos.svg#icono-persona"></use>
                    </svg>
                <?php endif; ?>
            </div>

            <!-- El input real va oculto; el label hace de botón -->
            <input
                type="file"
                name="foto"
                id="foto-perfil"
                class="cuenta__archivo"
                accept="image/png, image/jpeg, image/gif, image/webp">

            <label for="foto-perfil" class="cuenta__subir">
                Subir foto
            </label>

            <p class="cuenta__subir-ayuda">
                JPG, PNG, GIF o WEBP. Máximo 2 MB.
            </p>
        </div>

        <!-- ---------- Columna derecha: datos ---------- -->
        <div class="cuenta__form">

            <div class="cuenta__campo">
                <label for="cuenta-nombre">Nombre de usuario</label>
                <input
                    type="text"
                    name="nombre"
                    id="cuenta-nombre"
                    value="<?php echo escapar($usuario['nombre']); ?>"
                    maxlength="50"
                    required>
            </div>

            <div class="cuenta__campo">
                <label for="cuenta-password">Nueva contraseña</label>
                <input
                    type="password"
                    name="password"
                    id="cuenta-password"
                    placeholder="Déjala vacía para no cambiarla..."
                    autocomplete="new-password">
            </div>

            <div class="cuenta__campo">
                <label for="cuenta-password2">Repetir nueva contraseña</label>
                <input
                    type="password"
                    name="password2"
                    id="cuenta-password2"
                    placeholder="Repite la nueva contraseña..."
                    autocomplete="new-password">
            </div>

            <!-- ---------- Confirmación: sin esto no se cambian
                 el nombre ni la contraseña. ---------- -->
            <div class="cuenta__campo cuenta__campo--confirmar">
                <label for="cuenta-email-confirmacion">
                    Escribe tu correo para confirmar los cambios
                </label>
                <input
                    type="email"
                    name="email_confirmacion"
                    id="cuenta-email-confirmacion"
                    placeholder="Correo de tu cuenta..."
                    autocomplete="off">
                <span class="cuenta__ayuda">
                    Debe coincidir con el correo de tu cuenta.
                </span>
            </div>

            <div class="cuenta__acciones">
                <button type="submit" class="boton boton--principal">
                    Guardar cambios
                </button>

                <a href="index.php?pagina=home" class="boton boton--secundario">
                    Volver al inicio
                </a>

                <a href="index.php?pagina=logout" class="boton boton--salir">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </form>
</section>

<!-- ==========================
    Pagina modular de inicio de sesión y registro
    ========================== -->

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// No hace falta JS propio: el formulario se envía al servidor.
// (footer.php ya carga storage.js y login.js)
require __DIR__ . '/footer.php';
?>