<?php
// El CSS extra DEBE declararse antes de incluir el header:
// header.php es quien imprime los <link> a las hojas de estilo.
$cssExtra = ['public/css/paginaProducto.css'];

require __DIR__ . '/header.php';
?>

           <!-- ==========================
           DETALLE DEL PRODUCTO
           Imagen a la izquierda, ficha de compra a la derecha
        ========================== -->

           <section class="venta-producto">

            <?php
                $stockDisponible = (int) $producto['stock'];
            ?>

            <div class="detalle-producto">

                <!-- ---------- IZQUIERDA: imagen grande ---------- -->
                <div class="detalle-producto__imagen">
                    <img
                        src="public/img/ImagenesProductos/<?php echo htmlspecialchars($producto['imagen']); ?>"
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                </div>

                <!-- ---------- DERECHA: cuadro con la informacion ---------- -->
                <aside class="detalle-producto__ficha">

                    <span class="categoria-badge"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>

                    <h1 class="detalle-producto__titulo"><?php echo htmlspecialchars($producto['nombre']); ?></h1>

                    <p class="detalle-producto__marca">
                        <?php echo htmlspecialchars($producto['marca']); ?>
                    </p>

                    <p class="detalle-producto__descripcion">
                        <?php echo htmlspecialchars($producto['informacion'] ?? ''); ?>
                    </p>

                    <p class="detalle-producto__precio">
                        $<?php echo number_format($producto['precio'], 2, ',', '.'); ?>
                    </p>

                    <?php if ($stockDisponible > 0): ?>

                        <p class="detalle-producto__stock">
                            Stock disponible: <strong><?php echo $stockDisponible; ?></strong> unidades
                        </p>

                        <!-- ---------- contador de cantidad ---------- -->
                        <div class="detalle-producto__compra">
                            <label for="cantidad-detalle">Cantidad</label>

                            <div class="contador-cantidad">
                                <button
                                    type="button"
                                    class="contador-cantidad__boton"
                                    id="botonRestarCantidad"
                                    aria-label="Quitar una unidad">-</button>

                                <!-- El max lo pone el stock real del producto en la BD -->
                                <input
                                    type="number"
                                    id="cantidad-detalle"
                                    class="contador-cantidad__entrada"
                                    value="1"
                                    min="1"
                                    max="<?php echo $stockDisponible; ?>"
                                    inputmode="numeric">

                                <button
                                    type="button"
                                    class="contador-cantidad__boton"
                                    id="botonSumarCantidad"
                                    aria-label="Agregar una unidad">+</button>
                            </div>

                            <p class="detalle-producto__aviso-stock" id="avisoStock" hidden>
                                No hay más unidades disponibles.
                            </p>
                        </div>

                        <button
                            class="boton boton--principal boton-agregar-carrito"
                            data-id-producto="<?php echo $producto['id_producto']; ?>"
                            data-nombre-producto="<?php echo htmlspecialchars($producto['nombre']); ?>"
                            data-precio-producto="<?php echo $producto['precio']; ?>"
                            data-cantidad-origen="cantidad-detalle"
                            data-stock-producto="<?php echo $stockDisponible; ?>">
                            Agregar al carrito
                        </button>

                    <?php else: ?>

                        <p class="detalle-producto__stock sin-stock">
                            Sin stock por el momento
                        </p>

                        <button class="boton boton--principal" disabled>
                            Agregar al carrito
                        </button>

                    <?php endif; ?>

                    <a class="detalle-producto__volver" href="index.php?pagina=catalogo">
                        &larr; Volver al catálogo
                    </a>
                </aside>
            </div>

            <!-- ---------- Especificaciones ---------- -->
            <div class="especificaciones-producto">
                <details class="especificaciones-toggle">
                    <summary>Ver Especificaciones Técnicas</summary>

                    <div class="contenido-especificaciones">
                        <h3>Detalles del Producto</h3>
                        <p class="descripcion"><?php echo htmlspecialchars($producto['informacion'] ?? ''); ?></p>
                    </div>
                </details>
            </div>

        </section>



           <!-- ==========================
           DUDAS CLIENTE
        ========================== -->

           <section class="dudas">
                <div class="divisor-negro"></div>
                <h2>Opiniones del Producto</h2>
                <form action="">
                    <input type="text" name="question" id="question" placeholder = "Escribe una pregunta...">
                    <button type="submit">Enviar</button>
                </form>

                <details class="dudas-toggle">
                    <summary>Reseñas y Preguntas de otros Usuarios: </summary>
                    <div class="dudas-contenido">
                        <p>Aquí irán todas las reseñas que se encuentren en la BD! </p>
                    </div>
                </details>


            </section>

           <!-- ==========================
           PRODUCTOS RELACIONADOS
        ========================== -->
           <section class="productos-relacionados">
                <h2>
                    Más de <?php echo htmlspecialchars($producto['nombre_categoria']); ?>
                </h2>

                <?php if (empty($listaDeProductos)): ?>
                    <p class="mensaje-vacio">No hay otros productos de esta categoría por el momento.</p>
                <?php else: ?>
                    <div class="grilla-productos">
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
                    </div>
                <?php endif; ?>
            </section>



        <!-- ==========================
            Pagina modular de inicio de sesión y registro
            ========================== -->

        <?php require __DIR__ . '/modalLogin.php'; ?>

        <?php
// catalogo.js conecta el botón "Agregar al carrito" y el contador.
// (footer.php ya carga storage.js y login.js)
$scriptsExtra = ['public/js/catalogo.js'];
require __DIR__ . '/footer.php';
?>