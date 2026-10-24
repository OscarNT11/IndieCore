<?php
/* Formulario de alta y edición de producto. Recibe $producto, $categorias,
   $errores, $titulo y $esAlta desde AdminProductoController. */

$producto['nombre']      = $producto['nombre'] ?? '';
$producto['marca']       = $producto['marca'] ?? '';
$producto['informacion'] = $producto['informacion'] ?? '';
$producto['stock']       = $producto['stock'] ?? 0;
$producto['stock_minimo'] = $producto['stock_minimo'] ?? 0;
$producto['precio']      = $producto['precio'] ?? 0;
$producto['activo']      = $producto['activo'] ?? 1;
$producto['id_categoria'] = $producto['id_categoria'] ?? null;
?>

<section class="panel-admin panel-admin--sin-barra">

    <div class="panel-admin__contenido">

        <div class="panel-admin__cabecera">
            <h1><?= escapar($titulo) ?></h1>
            <a class="panel-admin__icono-boton" href="index.php?pagina=admin_producto" title="Volver a la lista de productos">
                <svg><use href="public/img/iconos.svg#icono-mas"></use></svg>
            </a>
        </div>

        <div class="panel-admin__tarjeta">

            <?php if (isset($errores['general'])): ?>
                <div class="text-danger" role="alert"><?= escapar($errores['general']) ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control" maxlength="255"
                           value="<?= escapar($producto['nombre']) ?>" required>
                    <?php if (isset($errores['nombre'])): ?>
                        <div class="text-danger"><?= escapar($errores['nombre']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="marca" class="form-label">Marca:</label>
                    <input type="text" name="marca" id="marca" class="form-control" maxlength="100"
                           value="<?= escapar($producto['marca']) ?>" required>
                    <?php if (isset($errores['marca'])): ?>
                        <div class="text-danger"><?= escapar($errores['marca']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="informacion" class="form-label">Información:</label>
                    <textarea name="informacion" id="informacion" class="form-control" rows="4" required><?= escapar($producto['informacion']) ?></textarea>
                    <?php if (isset($errores['informacion'])): ?>
                        <div class="text-danger"><?= escapar($errores['informacion']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="foto" class="form-label">Foto:</label>
                    <?php if (!empty($producto['foto'])): ?>
                        <div class="mb-2">
                            <img src="public/img/ImagenesProductos/<?= escapar($producto['foto']) ?>"
                                 alt="Foto de <?= escapar($producto['nombre']) ?>" style="max-width: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="foto" id="foto" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"
                           <?= $esAlta ? 'required' : '' ?>>
                    <small>JPG, PNG, GIF o WEBP. Máximo 2 MB.</small>
                    <?php if (isset($errores['foto'])): ?>
                        <div class="text-danger"><?= escapar($errores['foto']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="stock" class="form-label">Stock:</label>
                    <input type="number" name="stock" id="stock" class="form-control" min="0" max="999999"
                           value="<?= escapar($producto['stock']) ?>" required>
                    <?php if (isset($errores['stock'])): ?>
                        <div class="text-danger"><?= escapar($errores['stock']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="stock_minimo" class="form-label">Stock mínimo:</label>
                    <input type="number" name="stock_minimo" id="stock_minimo" class="form-control" min="0" max="999999"
                           value="<?= escapar($producto['stock_minimo']) ?>" required>
                    <?php if (isset($errores['stock_minimo'])): ?>
                        <div class="text-danger"><?= escapar($errores['stock_minimo']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="precio" class="form-label">Precio:</label>
                    <input type="number" step="0.01" min="0" max="999999.99" name="precio" id="precio" class="form-control"
                           value="<?= escapar($producto['precio']) ?>" required>
                    <?php if (isset($errores['precio'])): ?>
                        <div class="text-danger"><?= escapar($errores['precio']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="id_categoria" class="form-label">Categoría:</label>
                    <select name="id_categoria" id="id_categoria" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?= (int) $categoria['id_categoria'] ?>"
                                <?= ((int) $producto['id_categoria'] === (int) $categoria['id_categoria']) ? 'selected' : '' ?>>
                                <?= escapar($categoria['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errores['id_categoria'])): ?>
                        <div class="text-danger"><?= escapar($errores['id_categoria']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="activo" id="activo" class="form-check-input" value="1"
                           <?= ((int) $producto['activo'] === 1) ? 'checked' : '' ?>>
                    <label for="activo" class="form-check-label">Producto activo (visible en la tienda)</label>
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>
</section>

