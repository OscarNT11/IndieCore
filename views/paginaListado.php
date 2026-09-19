<?php require __DIR__ . '/header.php'; ?>

<section class="catalogo">
    <h2>Catalogos de Productos</h2>

    <div class="catalogo-layout">

        <!-- Tabla lateral con las categorías disponibles -->
        <aside class="catalogo-categorias">
            <table class="tabla-categorias">
                <caption>Categorías</caption>
                <tbody>
                    <tr>
                        <td>
                            <a
                                href="index.php?pagina=catalogo"
                                class="categoria-enlace <?php echo empty($idCategoriaSeleccionada) ? 'categoria-enlace--activa' : ''; ?>">
                                Todas las categorías
                            </a>
                        </td>
                    </tr>
                    <?php foreach ($listaDeCategorias as $categoria): ?>
                        <tr>
                            <td>
                                <a
                                    href="index.php?pagina=catalogo&categoria=<?php echo $categoria['id_categoria']; ?>"
                                    class="categoria-enlace <?php echo ($idCategoriaSeleccionada == $categoria['id_categoria']) ? 'categoria-enlace--activa' : ''; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </aside>

        <!-- Grilla de productos -->
        <div class="catalogo-productos">

    <?php if (empty($listaDeProductos)): ?>
        <p class="mensaje-vacio">No se encontraron productos en esta categoría.</p>
    <?php else: ?>
        <div class="grilla-productos">
            <?php foreach ($listaDeProductos as $producto): ?>
                <article class="tarjeta-producto">
                    <img
                        class="tarjeta-producto__imagen"
                        src="public/img/ImagenesProductos/<?php echo htmlspecialchars($producto['imagen']); ?>"
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>">

                    <span class="categoria-badge"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>
                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                    <p class="precio">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>
                    <p class="stock">Stock: <?php echo (int) $producto['stock']; ?></p>

                    <div class="tarjeta-producto__acciones">
                        <a
                            href="index.php?pagina=producto&id=<?php echo $producto['id_producto']; ?>"
                            class="boton boton--secundario">
                            Ver detalle
                        </a>

                        <button
                            class="boton boton--principal boton-agregar-carrito"
                            data-id-producto="<?php echo $producto['id_producto']; ?>"
                            data-nombre-producto="<?php echo htmlspecialchars($producto['nombre']); ?>"
                            data-precio-producto="<?php echo $producto['precio']; ?>"
                            <?php echo ($producto['stock'] <= 0) ? 'disabled' : ''; ?>>
                            Agregar al carrito
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

        </div><!-- /.catalogo-productos -->
    </div><!-- /.catalogo-layout -->
</section>

        <!-- ==========================
            Pagina modular de inicio de sesión y registro
            ========================== -->

        <?php require __DIR__ . '/modalLogin.php'; ?>

        <?php
// Esta vista necesita catalogo.js
// (footer.php ya carga storage.js y login.js)
$scriptExtra = 'public/js/catalogo.js';
require __DIR__ . '/footer.php';
?>
