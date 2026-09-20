<?php require __DIR__ . '/header.php'; ?>

<section class="carrito">

    <h1 class="carrito-titulo">Tu carrito</h1>

    <div class="carrito-layout">

        <!-- COLUMNA IZQUIERDA: productos (los genera carrito.js) -->
        <div class="carrito-productos">

            <div id="contenedorCarrito" class="carrito-lista"></div>

            <!-- Se muestra solo si el carrito está vacío (lo controla carrito.js) -->
            <p id="carritoVacio" class="carrito-vacio" hidden>
                Tu carrito está vacío.
                <a href="index.php?pagina=catalogo">Ir al catálogo</a>
            </p>
        </div>

        <!-- COLUMNA DERECHA: resumen y finalizar compra -->
        <aside id="resumenCarrito" class="carrito-resumen">
            <h2>Resumen de compra</h2>

            <h3 id="cantidad-carrito" class="carrito-cantidad">Cantidad de productos (0)</h3>
            <h3 id="totalCarrito" class="total">Total: $0,00</h3>

            <div class="carrito-acciones">
                <button id="botonVaciarCarrito" class="boton boton--secundario">Vaciar carrito</button>
                <button id="botonFinalizarCompra" class="boton boton--principal">Finalizar compra</button>
            </div>
        </aside>
    </div>
</section>

<?php
// Esta vista necesita carrito.js
// (footer.php ya carga storage.js y login.js)
$scriptsExtra = ['public/js/carrito.js'];
require __DIR__ . '/footer.php';
?>

            