<?php require __DIR__ . '/header.php'; ?>

<section class="carrito">

    <!-- Se muestra solo si el carrito está vacío (lo controla carrito.js) -->

    <div id="contenedorCarrito" class="carrito-lista">
    </div>

    <div id="resumenCarrito" class="carrito-resumen">
        <h3 id="totalCarrito" class="total">Total: $0</h3>
        <div class="carrito-acciones">
            <button id="botonVaciarCarrito" class="boton boton--secundario">Vaciar carrito</button>
            <button id="botonFinalizarCompra" class="boton boton--principal">Finalizar compra</button>
        </div>
    </div>
</section>

<?php
// Esta vista necesita además carrito.js
$scriptExtra = 'public/js/carrito.js';
require __DIR__ . '/footer.php';
?>

            