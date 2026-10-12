<?php
// ==========================
// Modal de inicio de sesión y registro.
// Se incluye desde cualquier vista con: require __DIR__ . '/modalLogin.php';
//
// Los formularios apuntan al router (index.php?pagina=login / registro),
// que los procesa en el servidor. Cada formulario lleva un campo oculto
// "volver" con la página en la que está el usuario para regresar ahí
// después de entrar o registrarse.
// ==========================

// config/auth.php ya está cargado por index.php; se incluye igual por si
// alguna vista abre el modal directamente.
require_once __DIR__ . '/../config/auth.php';

$paginaVolver = paginaActual();

// Si el modal se abrió desde la propia página de login/registro, volver a
// esa misma página significaría recargar el modal abierto. En ese caso se
// vuelve al inicio, que es una página "de fondo" normal.
if ($paginaVolver === 'login' || $paginaVolver === 'registro') {
    $paginaVolver = 'home';
}

$hayUsuarioLogueado = usuarioLogueado();
?>

<!-- ==========================
    Pagina modular de inicio de sesión
    ========================== -->

<div id="pantalla-transparente">
    <div id="ventana-login">
        <button class="cerrar-ventana">✕</button>
        <form id="login" action="index.php?pagina=login" method="POST" novalidate>
            <input type="hidden" name="volver" value="<?php echo $paginaVolver; ?>">
            <div class="campo nombre-usuario">
                <label for="ingresar-usua-login">Nombre de usuario o correo</label>
                <input type="text" name="usuario" placeholder="Ingrese usuario o correo..." class="espacio-texto" id="ingresar-usua-login" value="<?php echo escapar($usuarioIngresado ?? ''); ?>" required>
                <span class="mensaje-error" data-error-de="ingresar-usua-login" aria-live="polite"></span>
            </div>
            <div class="campo contraseña">
                <label for="ingresar-cont-login">Contraseña</label>
                <input type="password" name="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-login" required>
                <span class="mensaje-error" data-error-de="ingresar-cont-login" aria-live="polite"></span>
            </div>
            <div id="recordar">
                <label for="recordar-cont" id="recordar">
                    <br>
                    <input type="checkbox" name="recordar-cont" id="recordar-cont">
                    <p>Recordar en el dispositivo</p>
                </label>
            </div>

            <!-- Errores devueltos por el servidor (UsuarioController::login) -->
            <?php if (!empty($errores)): ?>
                <ul class="lista-errores" role="alert">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo escapar($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="boton">
                <button type="submit" class="enviar" id="boton-inicioSesion">
                    Iniciar Sesion
                </button>
            </div>
            <!-- ==========================
            CUENTA CREADA
            Un link que te lleva a registrarte por si
            no tienes una cuenta creada
            ========================== -->
            <div class="cuenta-creada">
                <p>No tienes una cuenta creada?</p>
                <!-- login.js abre la ventana de registro en la MISMA página
                     (evita el salto a index.php?pagina=registro). -->
                <a href="#" id="crear-cuenta">Crear cuenta</a>
            </div>
        </form>
    </div>
</div>

<!-- ==========================
    Pagina modular de registro
    ========================== -->

<div id="pantalla-registro">
    <div id="ventana-registro">
        <button class="cerrar-ventana">✕</button>

        <form id="register" action="index.php?pagina=registro" method="POST" novalidate>
            <input type="hidden" name="volver" value="<?php echo $paginaVolver; ?>">
            <div class="campo nombre-usuario">
                <label for="ingresar-usua-registro">Ingresar nombre de usuario</label>
                <input type="text" name="nombre" placeholder="Ingrese usuario..." class="espacio-texto" id="ingresar-usua-registro" value="<?php echo escapar($nombre ?? ''); ?>" required>
                <span class="mensaje-error" data-error-de="ingresar-usua-registro" aria-live="polite"></span>
            </div>
            <div class="campo" id="correo-electronico">
                <label for="ingresar-correo-registro">Ingresar correo electronico</label>
                <input type="email" name="email" placeholder="Ingrese correo electronico..." class="espacio-texto" id="ingresar-correo-registro" value="<?php echo escapar($email ?? ''); ?>" required>
                <span class="mensaje-error" data-error-de="ingresar-correo-registro" aria-live="polite"></span>
            </div>
            <div class="campo contraseña">
                <label for="ingresar-cont-registro">Ingresar contraseña</label>
                <input type="password" name="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-registro" required>
                <span class="mensaje-error" data-error-de="ingresar-cont-registro" aria-live="polite"></span>
            </div>
            <div class="campo" id="confirmar-contraseña">
                <label for="ingresar-conf-registro">Confirmar contraseña</label>
                <input type="password" name="password2" placeholder="Confirmar contraseña..." class="espacio-texto" id="ingresar-conf-registro" required>
                <span class="mensaje-error" data-error-de="ingresar-conf-registro" aria-live="polite"></span>
            </div>

            <!-- Errores devueltos por el servidor (UsuarioController::registro) -->
            <?php if (!empty($errores)): ?>
                <ul class="lista-errores" role="alert">
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo escapar($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="boton">
                <button type="submit" class="enviar" id="boton-Registrarse">
                    Registrarse
                </button>
            </div>
            <!-- ==========================
            CUENTA CREADA
            Un link que te lleva a iniciar sesion por si
            ya tienes una cuenta creada en la pagina
            ========================== -->
            <div class="cuenta-creada">
                <p>Ya tienes una cuenta?</p>
                <!-- login.js vuelve a la ventana de login en la MISMA página
                     (evita el salto a index.php?pagina=login). -->
                <a href="#" id="volver-login">Inicia sesión</a>
            </div>
        </form>
    </div>
</div>
