<?php
// Vista de inicio de sesión.
// Solo carga el modal: la página se ve de fondo con el header y el footer,
// y login.js abre automaticamente la ventana de login al detectar
// ?pagina=login (#pantalla-transparente).

require __DIR__ . '/header.php';
?>

<!-- ==========================
    Pagina modular de inicio de sesión y registro
    ========================== -->

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// login.js valida los formularios y abre el modal según ?pagina=.
// footer.php ya carga storage.js y login.js.
require __DIR__ . '/footer.php';
