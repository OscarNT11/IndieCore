<?php
// Vista de registro.
// Solo carga el modal (igual que el inicio de sesión): la página se ve de
// fondo con el header y el footer, y login.js abre automaticamente la
// ventana de registro al detectar ?pagina=registro (#pantalla-registro).
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
