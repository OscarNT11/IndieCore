<?php require __DIR__ . '/header.php'; ?>

           <section class="venta-producto">

            <div class="producto-principal">
                <img
                    class="imagen-producto"
                    src="public/img/productos/<?php echo htmlspecialchars($producto['imagen']); ?>"
                    alt="<?php echo htmlspecialchars($producto['nombre']); ?>">

                <div class="producto-informacion">
                    <span class="categoria-badge"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>
                    <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></p>

                    <p class="precio precio--grande">
                        $<?php echo number_format($producto['precio'], 2, ',', '.'); ?>
                    </p>

                    <p class="stock">
                        <?php if ($producto['stock'] > 0): ?>
                            Stock disponible: <?php echo (int) $producto['stock']; ?> unidades
                        <?php else: ?>
                            <span class="sin-stock">Sin stock por el momento</span>
                        <?php endif; ?>
                    </p>

                    <button
                            class="boton boton--principal boton-agregar-carrito"
                            data-id-producto="<?php echo $producto['id_producto']; ?>"
                            data-nombre-producto="<?php echo htmlspecialchars($producto['nombre']); ?>"
                            data-precio-producto="<?php echo $producto['precio']; ?>"
                            <?php echo ($producto['stock'] <= 0) ? 'disabled' : ''; ?>>
                            Agregar al carrito
                    </button>  
                </div>

            <div class="especificaciones-producto">
                <details class="especificaciones-toggle">
                <summary>Ver Especificaciones Técnicas</summary>

                <div class="contenido-especificaciones">
                    <h3>Detalles del Producto</h3>
                    <p class="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                </div>
                </details>
            </div>

        </div>



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
                <?php if (empty($listaDeProductos)): ?>
                    <p class="mensaje-vacio">No se encontraron productos relacionados.</p>
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