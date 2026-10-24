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

// Fecha del pedido tal como viene de la BD (o la de hoy si faltara).
$fechaDelPedido = !empty($pedido['fecha'])
    ? date('d/m/Y H:i', strtotime($pedido['fecha']))
    : date('d/m/Y H:i');

// Número de factura con formato legible: 000013 para el pedido 13.
$numeroDeFactura = str_pad((string) (int) $pedido['id_pedido'], 6, '0', STR_PAD_LEFT);

// Total de unidades compradas (columna Cantidad de la tabla).
$unidadesTotales = 0;

foreach ($detallesDelPedido as $detalleContado) {
    $unidadesTotales += (int) $detalleContado['cantidad'];
}

// Marca de la empresa que emite la factura.
$datosDeLaEmpresa = array(
    'nombre'    => 'Tecno Parts',
    'direccion' => 'Av. 18 de Julio 1234, Montevideo, Uruguay',
    'contacto'  => 'Tel. 095 756 103',
);

// Un gestor puede abrir la factura de otro usuario desde el panel de pedidos.
// En ese caso el pedido es ajeno: no se toca el carrito ni se ofrece "mi cuenta".
$vistoDesdePanel = !empty($vistoDesdePanel);
$clienteDelPedido = $clienteDelPedido ?? null;
?>

<section class="pedido-confirmado">

    <!-- Aviso de resultado (no se imprime: es solo para la pantalla) -->
    <div class="pedido-confirmado__aviso no-imprimir">
        <?php if ($vistoDesdePanel): ?>
            <h1>Factura confirmada</h1>
            <p>
                Pedido <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong>
                hecho por
                <?php echo escapar($clienteDelPedido['nombre'] ?? 'un usuario'); ?>.
                <?php if (!empty($clienteDelPedido) && (int) $clienteDelPedido['id_usuario'] === (int) $idUsuario): ?>
                    Es tu propio pedido.
                <?php endif; ?>
            </p>
        <?php elseif ($estadoDelPedido === 'rechazado'): ?>
            <h1>No pudimos procesar el pago</h1>
            <p>
                Tu pedido quedó registrado con el número
                <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong>,
                pero el pago fue rechazado. Podés reintentarlo desde el carrito.
            </p>
        <?php elseif ($estadoDelPedido === 'pendiente'): ?>
            <h1>¡Gracias por tu compra!</h1>
            <p>
                Tu pedido quedó registrado con el número
                <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong>.
                El pago queda <strong>pendiente</strong>: se confirma al recibir el
                efectivo o al acreditarse la transferencia.
            </p>
        <?php else: ?>
            <h1>¡Gracias por tu compra!</h1>
            <p>
                Tu pedido quedó registrado con el número
                <strong>#<?php echo (int) $pedido['id_pedido']; ?></strong>.
            </p>
        <?php endif; ?>
    </div>

    <!-- ==========================
         FACTURA DEL PEDIDO
         Este bloque es lo que se imprime. Los botones (no-imprimir)
         quedan fuera de la impresión.
         ========================== -->
    <article class="factura" id="facturaPedido">

        <!-- ---------- Encabezado: empresa y datos de la factura ---------- -->
        <header class="factura__cabecera">

            <div class="factura__empresa">
                <img
                    class="factura__logo"
                    src="public/img/logoTecnoParts.png"
                    alt="Logo de <?php echo escapar($datosDeLaEmpresa['nombre']); ?>">

                <div class="factura__empresa-datos">
                    <h1><?php echo escapar($datosDeLaEmpresa['nombre']); ?></h1>
                    <p><?php echo escapar($datosDeLaEmpresa['direccion']); ?></p>
                    <p><?php echo escapar($datosDeLaEmpresa['contacto']); ?></p>
                </div>
            </div>

            <div class="factura__identificacion">
                <p class="factura__titulo-doc">FACTURA</p>
                <p class="factura__numero">N.º <?php echo escapar($numeroDeFactura); ?></p>
                <p class="factura__dato"><span>Fecha</span><?php echo escapar($fechaDelPedido); ?></p>
                <p class="factura__dato">
                    <span>Pedido</span>#<?php echo (int) $pedido['id_pedido']; ?>
                </p>

                <p class="pedido-confirmado__estado pedido-confirmado__estado--<?php echo escapar($estadoDelPedido); ?> factura__estado">
                    <?php echo escapar($estadosDelPedido[$estadoDelPedido] ?? $estadoDelPedido); ?>
                </p>
            </div>
        </header>

        <!-- ---------- Cliente y envío ---------- -->
        <div class="factura__partes">

            <section class="factura__parte">
                <h2>Facturado a</h2>
                <p class="factura__parte-nombre"><?php echo escapar($pedido['envio_nombre'] ?? ''); ?></p>
                <p><?php echo escapar($pedido['envio_direccion'] ?? ''); ?></p>
                <p>
                    <?php echo escapar($pedido['envio_ciudad'] ?? ''); ?>,
                    <?php echo escapar($pedido['envio_departamento'] ?? ''); ?>
                </p>
                <p>Teléfono: <?php echo escapar($pedido['envio_telefono'] ?? ''); ?></p>

                <?php if (!empty($clienteDelPedido)): ?>
                    <p class="factura__cuenta">
                        Cuenta: <?php echo escapar($clienteDelPedido['nombre']); ?>
                        (<?php echo escapar($clienteDelPedido['email']); ?>)
                    </p>
                <?php endif; ?>
            </section>

            <section class="factura__parte">
                <h2>Método de pago</h2>
                <p class="factura__parte-nombre">
                    <?php if (!empty($pagoDelPedido)): ?>
                        <?php echo escapar($metodosDePago[$pagoDelPedido['metodo_pago']] ?? $pagoDelPedido['metodo_pago']); ?>
                    <?php else: ?>
                        Sin método de pago registrado
                    <?php endif; ?>
                </p>

                <?php if (!empty($pagoDelPedido['ultimos4'])): ?>
                    <p>Tarjeta terminada en <?php echo escapar($pagoDelPedido['ultimos4']); ?></p>
                <?php endif; ?>

                <?php if (!empty($pagoDelPedido['fecha_pago'])): ?>
                    <p>Registrado el <?php echo escapar(date('d/m/Y H:i', strtotime($pagoDelPedido['fecha_pago']))); ?></p>
                <?php endif; ?>
            </section>
        </div>

        <!-- ---------- Detalle de productos ---------- -->
        <table class="tabla-resumen-pedido factura__tabla">
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

        <!-- ---------- Totales ---------- -->
        <div class="factura__totales">
            <p class="factura__linea-total">
                <span>Unidades</span>
                <?php echo (int) $unidadesTotales; ?>
            </p>

            <p class="factura__linea-total factura__linea-total--final">
                <span>Total del pedido</span>
                $<?php echo number_format((float) $totalDelPedido, 2, ',', '.'); ?>
            </p>
        </div>

        <!-- ---------- Pie de la factura ---------- -->
        <footer class="factura__pie">
            <p>
                Documento generado automáticamente por <?php echo escapar($datosDeLaEmpresa['nombre']); ?>.
                Gracias por tu compra.
            </p>
        </footer>
    </article>

    <!-- ---------- Acciones (no se imprimen) ---------- -->
    <div class="pedido-confirmado__acciones no-imprimir">
        <?php if ($vistoDesdePanel): ?>

            <button type="button" class="boton boton--secundario" id="botonImprimirFactura">
                Imprimir factura
            </button>

            <a href="index.php?pagina=admin_pedidos" class="boton boton--secundario">
                Volver a pedidos
            </a>

            <a href="index.php?pagina=panel" class="boton boton--principal">
                Volver al panel
            </a>

        <?php else: ?>

            <button type="button" class="boton boton--secundario" id="botonImprimirFactura">
                Imprimir factura
            </button>

            <a href="index.php?pagina=cuenta" class="boton boton--secundario">
                Volver a mi cuenta
            </a>

            <a href="index.php?pagina=catalogo" class="boton boton--principal">
                Seguir comprando
            </a>

        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// El pedido ya está guardado: se vacía el carrito del navegador para no
// volver a comprar lo mismo. Con el pago RECHAZADO no se vacía: el carrito
// se conserva para que el usuario pueda reintentar el pago.
//
// pedido.js solo funciona en esta página (botón "Imprimir factura").
$scriptsExtra = ['public/js/pedido.js'];

$scriptEnLinea = "";

// Solo se vacía el carrito cuando el dueño del pedido acaba de comprar. Si un
// gestor abre la factura de otra persona, su carrito no tiene nada que ver.
if ($estadoDelPedido !== 'rechazado' && !$vistoDesdePanel) {
    $scriptEnLinea = "
        actualizarContador();
    ";
}

require __DIR__ . '/footer.php';
?>
