<?php
/* Listado de todos los pedidos del panel. Recibe $pedidos (cada uno con
   cliente_nombre, cliente_email, metodo_pago, ultimos4 y unidades) y
   $resumen (total_pedidos / total_facturado) desde AdminPedidoController. */

$nombresDeEstado = [
    'pendiente' => 'Pago pendiente',
    'pagado'    => 'Pago aprobado',
    'rechazado' => 'Pago rechazado',
    'enviado'   => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
];

$nombresDeMetodo = [
    'tarjeta_credito' => 'Tarjeta de crédito',
    'tarjeta_debito'  => 'Tarjeta de débito',
    'transferencia'   => 'Transferencia bancaria',
    'efectivo'        => 'Efectivo al recibir',
];
?>

<section class="panel-admin panel-admin--sin-barra">

    <div class="panel-admin__contenido panel-productos panel-pedidos">

    <div class="panel-admin__cabecera">
        <h1>Pedidos</h1>
    </div>

    <p class="panel-productos__acciones">
        <a href="index.php?pagina=panel">Volver al panel de gestión</a>
    </p>

    <div class="panel__tarjetas panel-pedidos__resumen">
        <div class="panel__tarjeta panel-pedidos__dato">
            <h2><?= (int) $resumen['total_pedidos'] ?></h2>
            <p>Pedidos registrados</p>
        </div>

        <div class="panel__tarjeta panel-pedidos__dato">
            <h2>$<?= number_format((float) $resumen['total_facturado'], 2, ',', '.') ?></h2>
            <p>Total facturado (sin cancelados)</p>
        </div>
    </div>

    <?php if (empty($pedidos)): ?>
        <p>Todavía no hay pedidos registrados.</p>
    <?php else: ?>
        <div class="panel-productos__tabla"><table class="table table-striped">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Artículos</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Factura</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <?php
                        $estado = $pedido['estado'] ?? 'pendiente';
                        $numeroFactura = str_pad((string) (int) $pedido['id_pedido'], 6, '0', STR_PAD_LEFT);
                        $fecha = !empty($pedido['fecha'])
                            ? date('d/m/Y H:i', strtotime($pedido['fecha']))
                            : '';
                        $metodo = $pedido['metodo_pago'] ?? '';
                    ?>
                    <tr>
                        <td data-etiqueta="Factura">N.º <?= escapar($numeroFactura) ?></td>
                        <td data-etiqueta="Fecha"><?= escapar($fecha) ?></td>
                        <td data-etiqueta="Cliente">
                            <?= escapar($pedido['cliente_nombre']) ?><br>
                            <small><?= escapar($pedido['cliente_email']) ?></small>
                        </td>
                        <td data-etiqueta="Artículos"><?= (int) ($pedido['unidades'] ?? 0) ?> u.</td>
                        <td data-etiqueta="Pago">
                            <?= escapar($nombresDeMetodo[$metodo] ?? 'Sin método de pago') ?>
                            <?php if (!empty($pedido['ultimos4'])): ?>
                                <br><small>•••• <?= escapar($pedido['ultimos4']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td data-etiqueta="Estado">
                            <span class="pedido-confirmado__estado pedido-confirmado__estado--<?= escapar($estado) ?> stock">
                                <?= escapar($nombresDeEstado[$estado] ?? $estado) ?>
                            </span>
                        </td>
                        <td data-etiqueta="Total">$<?= number_format((float) $pedido['precio_total'], 2, ',', '.') ?></td>
                        <td data-etiqueta="">
                            <a href="index.php?pagina=pedido&id=<?= (int) $pedido['id_pedido'] ?>&panel=1"
                               class="btn btn-sm btn-primary" target="_blank" rel="noopener">
                                Ver factura
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>

    </div>
</section>
