<?php
function escapar ($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/* Indica si un nombre de usuario es "limpio": solo letras (incluidas tildes,
   ñ, diéresis), números, espacios y los signos _ - .  Cualquier otro carácter
   (los típicos de un ataque XSS: < > " ' ; & / \ etc.) lo invalida.  Se usa
   en el registro y al cambiar el nombre para no guardar código en la BD. */
function nombreUsuarioValido($nombre) {
    return preg_match('/^[\p{L}\p{N} _.-]+$/u', (string) $nombre) === 1;
}

function redirigir($pagina) {
header("Location: index.php?pagina=" . $pagina);
exit;
}

/* Devuelve la página en la que está el usuario (el valor de ?pagina=),
   para el campo oculto "volver" de los formularios del modal.
   Se usa en views/modalLogin.php: sin esta función la vista lanzaba
   un "Call to undefined function" que cortaba la página antes del
   footer (por eso el footer nunca se veía). */
function paginaActual() {
    $pagina = isset($_GET['pagina']) ? (string) $_GET['pagina'] : 'home';

    // Solo se acepta un nombre de página sencillo: nada de rutas ni
    // parámetros, para que no se cuele una URL externa en el form.
    $pagina = preg_replace('/[^a-zA-Z0-9_-]/', '', $pagina);

    return $pagina === '' ? 'home' : $pagina;
}

/* Indica si hay una sesión iniciada. La sesión la abre index.php al
   arrancar; aquí NO se llama a session_start() porque estas funciones
   se usan desde las vistas, cuando el HTML ya se está enviando, y PHP
   no puede mandar la cookie de sesión a esas alturas. */
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

