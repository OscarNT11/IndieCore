<?php
function escapar ($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

// Se encarga de restringir caracteres para bloquear cualquier tipo de inyeccion sql.
function nombreUsuarioValido($nombre) {
    return preg_match('/^[\p{L}\p{N} _.-]+$/u', (string) $nombre) === 1;
}


function redirigir($pagina) {
    header("Location: index.php?pagina=" . $pagina . $extra);
    exit;
}

/* Devuelve la pagina en la que esta el usuario (el valor de ?pagina=),
   para el campo oculto "volver" de los formularios del modal
   (views/modalLogin.php). */
function paginaActual() {
    $pagina = isset($_GET['pagina']) ? (string) $_GET['pagina'] : 'home';

    // Solo se acepta un nombre de página sencillo: nada de rutas ni
    // parámetros, para que no se cuele una URL externa en el form.
    $pagina = preg_replace('/[^a-zA-Z0-9_-]/', '', $pagina);

    return $pagina === '' ? 'home' : $pagina;
}

/* Indica si hay una sesion iniciada. La sesion la abre index.php al
   arrancar; aqui NO se llama a session_start() porque estas funciones
   se usan desde las vistas, cuando el HTML ya se esta enviando, y PHP
   no puede mandar la cookie de sesion a esas alturas. */

function usuarioLogueado() {
    return !empty($_SESSION['id_usuario']);
}

/* Datos del usuario logueado (o null si no hay sesión). */
function usuarioActual() {
    if (!usuarioLogueado()) {
        return null;
    }

    return array(
        'id_usuario' => $_SESSION['id_usuario'],
        'nombre'     => $_SESSION['nombre_usuario'] ?? '',
        'email'      => $_SESSION['email_usuario'] ?? '',
        'foto'       => $_SESSION['foto_usuario'] ?? '',
    );
}

/* Rol del usuario logueado (o null). Los valores posibles son los del
   ENUM de la tabla `usuarios`: 'cliente', 'administrador' u 'operador'. */
function usuarioActualRol() {
    return $_SESSION['rol'] ?? null;
}

/** ¿El usuario logueado es administrador? */
function esAdmin() {
    return usuarioActualRol() === 'administrador';
}

/** ¿El usuario logueado es operador? */
function esOper() {
    return usuarioActualRol() === 'operador';
}

/** ¿El usuario logueado puede entrar al panel de gestión? (admin u operador) */
function esGestor() {
    return esAdmin() || esOper();
}

/* Guarda en la sesión los datos del usuario que acaba de loguearse.
   Recibe la fila de la tabla `usuarios` (arreglo). */
function iniciarSesionUsuario($usuario) {
    session_regenerate_id(true); // cambia el ticket (evita fijación de sesión)

    $_SESSION['id_usuario']     = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre'];
    $_SESSION['email_usuario']  = $usuario['email'] ?? '';
    $_SESSION['foto_usuario']   = $usuario['foto'] ?? '';
    $_SESSION['rol']            = $usuario['rol'] ?? 'cliente';
}

/** Cierra la sesión completamente. */
function cerrarSesion() {
    $_SESSION = [];
    session_destroy();
}

/** Si no está logueado, manda al login. */
function requerirLogin() {
    if (!usuarioLogueado()) {
        redirigir('login');
    }
}

/** Exige estar logueado y tener un rol determinado. */
function requerirRol($rol) {
    requerirLogin();

    if (usuarioActualRol() !== $rol) {
        http_response_code(403); // 403 = Prohibido
        require __DIR__ . '/../views/sin_permiso.php';
        exit;
    }
}