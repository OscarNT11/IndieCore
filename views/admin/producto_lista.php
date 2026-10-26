<?php
/* Listado de productos del panel. Recibe $productos (cada uno con
   nivel_stock y nombre_categoria) y $aviso desde AdminProductoController. */

$etiquetas = [
    'agotado' => 'sin stock',
    'bajo'    => 'bajo stock',
    'normal'  => 'stock normal',
];
?>

<section class="panel-admin panel-admin--sin-barra">

    <div class="panel-admin__contenido panel-productos">

    <div class="panel-admin__cabecera">
        <h1>Lista de productos</h1>
        <a class="panel-admin__icono-boton" href="index.php?pagina=admin_producto_crear" title="Agregar producto">
            <svg><use href="public/img/iconos.svg#icono-mas"></use></svg>
        </a>
    </div>

    <p class="panel-productos__acciones">
        <a href="index.php?pagina=panel">Volver al panel de gestión</a>
    </p>

    <?php if ($aviso !== ''): ?>
        <div class="alert alert-success" role="status"><?= escapar($aviso) ?></div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <p>No hay productos registrados.</p>
    <?php else: ?>
        <div class="panel-productos__tabla"><table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Foto</th>
                    <th>Stock</th>
                    <th>Precio</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td data-etiqueta="Nombre"><?= escapar($producto['nombre']) ?></td>
                        <td data-etiqueta="Foto">
                            <?php if (!empty($producto['imagen'])): ?>
                                <img src="public/img/ImagenesProductos/<?= escapar($producto['imagen']) ?>"
                                     alt="Foto de <?= escapar($producto['nombre']) ?>" >
                            <?php else: ?>
                                Sin foto
                            <?php endif; ?>
                        </td>
                        <td data-etiqueta="Stock"><?= (int) $producto['stock'] ?></td>
                        <td data-etiqueta="Precio">$<?= number_format((float) $producto['precio'], 2, ',', '.') ?></td>
                        <td data-etiqueta="Categoría"><?= escapar($producto['nombre_categoria']) ?></td>
                        <td data-etiqueta="Estado">
                            <span class="stock stock-<?= escapar($producto['nivel_stock']) ?>">
                                <?= escapar($etiquetas[$producto['nivel_stock']] ?? '') ?>
                            </span>
                            <?php if ((int) $producto['activo'] !== 1): ?>
                                <span class="stock stock-inactivo">inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td data-etiqueta="Acciones">
                            <a href="index.php?pagina=admin_producto_editar&id=<?= (int) $producto['id_producto'] ?>" class="btn btn-sm btn-warning">
                                Editar
                            </a>
                            <a href="index.php?pagina=admin_producto_estado&id=<?= (int) $producto['id_producto'] ?>" class="btn btn-sm btn-danger">
                                <?= ((int) $producto['activo'] === 1) ? 'Desactivar' : 'Reactivar' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>

    </div>
</section>
