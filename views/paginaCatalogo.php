<?php require __DIR__ . '/header.php'; ?>

<?php
// Si la búsqueda del header mandó un término (?q=), el controlador rellenó
// $listaDeProductos con los resultados y aquí se avisa de ello.
$terminoBusqueda = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
?>

<section class="catalogo">

    <button type="button" class="btn-filtro">
        <svg><use href="public/img/iconos.svg#icono-filtro"></use></svg>
    </button>
    
    <h2>
            <?php if ($terminoBusqueda !== ''): ?>
                Resultados para "<?php echo htmlspecialchars($terminoBusqueda); ?>"
            <?php else: ?>
                Catalogos de Productos
            <?php endif; ?>
    </h2>

    <div class="catalogo-layout">

        <!-- Tabla lateral con las categorías disponibles -->
        <aside class="catalogo-categorias ocultar-filtro">

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


        <!-- Catalogo de productos -->
        <div class="catalogo-productos">

            <?php if (empty($listaDeProductos)): ?>
                <?php if ($terminoBusqueda !== ''): ?>
                
                    <p class="mensaje-vacio">
                        No se encontraron productos para "<?php echo htmlspecialchars($terminoBusqueda); ?>".
                        <a href="index.php?pagina=catalogo">Ver todo el catálogo</a>
                    </p>

                <?php else: ?>
                    <p class="mensaje-vacio">No se encontraron productos en esta categoría.</p>
                <?php endif; ?>

            <?php else: ?>
                
                <?php foreach ($listaDeProductos as $producto): ?>
                    <article class="tarjeta-producto">
                       <a
                                class="tarjeta-producto__enlace"
                                href="index.php?pagina=producto&id=<?php echo $producto['id_producto']; ?>"
                                title="Ver detalle de <?php echo htmlspecialchars($producto['nombre']); ?>">
                                <img
                                    class="tarjeta-producto__imagen"
                                    src="public/img/ImagenesProductos/<?php echo htmlspecialchars($producto['imagen']); ?>"
                                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        </a>

                        <span class="categoria-badge"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>
                        <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                        <p class="precio">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>
                        <div class="tarjeta-producto__estrellas">
                            <?php echo estrellasHtml($producto['promedio_estrellas'] ?? 0); ?>
                            <span><?php echo escapar(resumenEstrellas($producto['promedio_estrellas'] ?? 0, $producto['total_resenias'] ?? 0)); ?></span>
                        </div>
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
                                data-stock-producto="<?php echo (int) $producto['stock']; ?>"
                                <?php echo ($producto['stock'] <= 0) ? 'disabled' : ''; ?>>
                                Agregar al carrito
                            </button>
                        </div>

                    </article>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
         
    </div><!-- /.catalogo-layout -->

</section><!-- /.catalogo  -->








        <!-- ==========================
            Pagina modular de inicio de sesión y registro
            ========================== -->

        <?php require __DIR__ . '/modalLogin.php'; ?>

        <?php
// Esta vista necesita catalogo.js para los botones "Agregar al carrito"
// y carrusel.js para las flechas cuando hay más de 4 productos.
// (footer.php ya carga storage.js y login.js)
$scriptsExtra = ['public/js/catalogo.js', 'public/js/carrusel.js', "public/js/filtrado.js"];
require __DIR__ . '/footer.php';
?>
