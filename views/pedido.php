<?php
// La hoja de esta vista debe fijarse antes de header.php, que lee $cssExtra.
$cssExtra = ['public/css/paginaPedido.css'];
require __DIR__ . '/header.php';
?>

<section class="pedido-confirmado">
    <h1>¡Gracias por tu compra!</h1>
    <p>Tu pedido quedó registrado con el número <strong>#<?php echo (int) $idPedidoCreado; ?></strong>.</p>

    <table class="tabla-resumen-pedido">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($carritoRecibido as $itemDelCarrito): ?>
                <tr>
                    <td><?php echo htmlspecialchars($itemDelCarrito['nombre'] ?? ''); ?></td>
                    <td><?php echo (int) $itemDelCarrito['cantidad']; ?></td>
                    <td>$<?php echo number_format($itemDelCarrito['precio'], 2, ',', '.'); ?></td>
                    <td>$<?php echo number_format($itemDelCarrito['precio'] * $itemDelCarrito['cantidad'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total-pedido-confirmado">
        Total del pedido: $<?php echo number_format($totalDelPedido, 2, ',', '.'); ?>
    </p>

    <a href="index.php?pagina=catalogo" class="boton boton--principal">Seguir comprando</a>
</section>

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// El pedido ya está guardado: se vacía el carrito del navegador.
$scriptEnLinea = "
    guardarCarrito([]);
    actualizarContador();
";
require __DIR__ . '/footer.php';
?>
