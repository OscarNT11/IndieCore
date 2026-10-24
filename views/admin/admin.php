<section class="panel-alerta-stock">
    <h2>Alertas de stock</h2>

    <?php if (empty($productosStockBajo)): ?>
        <p>Todo en orden: ningún producto está por debajo de su stock mínimo.</p>
    <?php else: ?>
        <p class="aviso-stock">
            <?= count($productosStockBajo) ?> producto(s) con stock bajo o agotado.
        </p>

        <table class="tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Stock</th>
                    <th>Mínimo</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productosStockBajo as $p): ?>
                    <tr>
                        <td><?= escapar($p['nombre']) ?></td>
                        <td><?= (int) $p['stock'] ?></td>
                        <td><?= (int) $p['stock_minimo'] ?></td>
                        <td>
                            <span class="stock stock-<?= escapar($p['nivel_stock']) ?>">
                                <?= ($p['nivel_stock'] === 'agotado') ? 'Sin stock' : 'Stock bajo' ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?pagina=admin_producto_editar&id=<?= (int) $p['id_producto'] ?>">
                                Reponer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
