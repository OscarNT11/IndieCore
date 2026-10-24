<?php require __DIR__ . '/header.php'; ?>



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


        <!-- Tabla lateral de filtraje -->
        <aside class="catalogo-categorias ocultar-filtro">
            <!-- Envolvemos todo en un formulario GET para que envíe los filtros -->
            <form action="index.php" method="GET" id="form-filtros">
                <!-- Mantenemos la página actual en el formulario para no perder la ruta del catálogo -->
                <input type="hidden" name="pagina" value="catalogo">

                <!-- Categorías (Radio: una sola opción) -->
                <div class="filtro-grupo categorias-group">
                    <h3>Categorías</h3>
                    
                    <!-- Seleccion "Predeterminada" -->
                    <label class="radio-label">
                        <input type="radio" name="categoria" value="" <?php echo empty($idCategoriaSeleccionada) ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <span>Todas</span>
                    </label>

                    <!-- Genera todas las opciones de a partir de $listaDeCategorias -->
                    <?php foreach ($listaDeCategorias as $categoria): ?>
                        <label class="radio-label">
                            <input type="radio" name="categoria" value="<?php echo $categoria['id_categoria']; ?>" 
                                <?php echo ($idCategoriaSeleccionada == $categoria['id_categoria']) ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <span><?php echo htmlspecialchars($categoria['nombre']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <!-- Precio -->
                <div class="filtro-grupo">
                    <h3>Precio ($)</h3>
                    <div class="precio-inputs">
                    <input type="number" id="precio-min" name="precio_min" placeholder="Mín" min="0" value="<?php echo htmlspecialchars($precioMin ?? ''); ?>">
                    <span>-</span>
                    <input type="number" id="precio-max" name="precio_max" placeholder="Máx" min="0" value="<?php echo htmlspecialchars($precioMax ?? ''); ?>">
                    <button type="submit" id="btn-filtrar-precio">OK</button>
                    </div>
                </div>

                <!-- Marcas (Checkbox: múltiple opción) -->
                <div class="filtro-grupo">
                    <h3>Marcas</h3>
                    
                    <!-- Input para buscar/filtrar marcas en tiempo real -->
                    <div class="marcas-buscador-wrapper" style="padding: 0.5rem 1rem;">
                        <input type="text" id="buscador-marca" placeholder="Buscar otras marcas..." autocomplete="off">
                    </div>

                    <div class="marcas-container">
                        <?php 
                        // Array estático de ejemplo con 5 marcas (puedes cambiarlo o hacerlo dinámico después)
                        // Por ahora es así porque marca es atributo, no tabla propia (en la bd)
                        $marcasDisponibles = ['Asus', 'MSI', 'Gigabyte', 'Corsair', 'Kingston', "Sony", "Apple"];
                        // Obtenemos las marcas que ya estaban seleccionadas en la URL (si las hay)
                        $marcasSeleccionadas = isset($_GET['marca']) ? (array)$_GET['marca'] : [];

                        foreach ($marcasDisponibles as $marca): 
                            $marcaSlug = strtolower($marca);
                            $isChecked = in_array($marcaSlug, $marcasSeleccionadas) ? 'checked' : '';
                            
                        ?>
                            <label class="checkbox-label" data-nombre="<?php echo strtolower($marca); ?>">
                                <input type="checkbox" name="marca[]" value="<?php echo $marcaSlug; ?>" <?php echo $isChecked; ?> onchange="this.form.submit()"> 
                                <?php echo $marca; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </form>
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
