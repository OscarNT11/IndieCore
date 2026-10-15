<?php
// La hoja de esta vista debe fijarse antes de header.php, que lee $cssExtra.
$cssExtra = ['public/css/paginaPedido.css'];
require __DIR__ . '/header.php';

// ==========================
// Página final del pedido.
// La carga PedidoController::mostrarConfirmacion() con datos LEÍDOS de la BD:
//   $pedido            -> fila de `pedidos`
//   $detallesDelPedido -> filas de `detalles_pedido`
//   $pagoDelPedido     -> último pago registrado (o null)
//   $totalDelPedido    -> total guardado
// ==========================

$estadoDelPedido = $pedido['estado'] ?? 'pendiente';

$metodosDePago = array(
    'tarjeta_credito' => 'Tarjeta de crédito',
    'tarjeta_debito'  => 'Tarjeta de débito',
    'transferencia'   => 'Transferencia bancaria',
    'efectivo'        => 'Efectivo al recibir',
);

$estadosDelPedido = array(
    'pendiente' => 'Pago pendiente',
    'pagado'    => 'Pago aprobado',
    'rechazado' => 'Pago rechazado',
    'enviado'   => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
);
?>

<section class="pedido-confirmado">

    <?php if ($estadoDelPedido === 'rechazado'): ?>
        <h1>No pudimos procesar el pago</h1>
        <p>
            Tu pedido quedó registrado con el número
            <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong>,
            pero el pago fue rechazado. Podés reintentarlo desde el carrito.
        </p>
    <?php else: ?>
        <h1>¡Gracias por tu compra!</h1>
        <p>
            Tu pedido quedó registrado con el número
            <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong>.
        </p>
    <?php endif; ?>

    <p class="pedido-confirmado__estado pedido-confirmado__estado--<?php echo escapar($estadoDelPedido); ?>">
        <?php echo escapar($estadosDelPedido[$estadoDelPedido] ?? $estadoDelPedido); ?>
    </p>

    <?php if (!empty($pagoDelPedido)): ?>
        <p class="pedido-confirmado__pago">
            Método de pago:
            <strong><?php echo escapar($metodosDePago[$pagoDelPedido['metodo_pago']] ?? $pagoDelPedido['metodo_pago']); ?></strong>
            <?php if (!empty($pagoDelPedido['ultimos4'])): ?>
                (tarjeta terminada en <?php echo escapar($pagoDelPedido['ultimos4']); ?>)
            <?php endif; ?>
        </p>
    <?php endif; ?>

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
            <?php foreach ($detallesDelPedido as $detalle): ?>
                <tr>
                    <td><?php echo escapar($detalle['nombre_producto'] ?? ('Producto #' . $detalle['id_producto'])); ?></td>
                    <td><?php echo (int) $detalle['cantidad']; ?></td>
                    <td>$<?php echo number_format((float) $detalle['precio_unitario'], 2, ',', '.'); ?></td>
                    <td>$<?php echo number_format((float) $detalle['precio_unitario'] * (int) $detalle['cantidad'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total-pedido-confirmado">
        Total del pedido: $<?php echo number_format((float) $totalDelPedido, 2, ',', '.'); ?>
    </p>

    <!-- ---------- Datos de envío guardados con el pedido ---------- -->
    <div class="pedido-confirmado__envio">
        <h2>Datos de envío</h2>

        <p><strong><?php echo escapar($pedido['envio_nombre'] ?? ''); ?></strong></p>
        <p><?php echo escapar($pedido['envio_direccion'] ?? ''); ?></p>
        <p>
            <?php echo escapar($pedido['envio_ciudad'] ?? ''); ?>,
            <?php echo escapar($pedido['envio_departamento'] ?? ''); ?>
        </p>
        <p>Teléfono: <?php echo escapar($pedido['envio_telefono'] ?? ''); ?></p>
    </div>

    <a href="index.php?pagina=catalogo" class="boton boton--principal">Seguir comprando</a>
</section>

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// El pedido ya está guardado: se vacía el carrito del navegador para no
// volver a comprar lo mismo. Con el pago RECHAZADO no se vacía: el carrito
// se conserva para que el usuario pueda reintentar el pago.
$scriptEnLinea = "";

if ($estadoDelPedido !== 'rechazado') {
    $scriptEnLinea = "
        guardarCarrito([]);
        actualizarContador();
    ";
}

require __DIR__ . '/footer.php';
?>
