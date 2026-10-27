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

                    <a href="index.php?pagina=catalogo&categoria=<?php echo $producto['id_categoria']; ?>">
                        <span class="categoria-badge"><?php echo htmlspecialchars($producto['nombre_categoria']); ?></span>
                    </a>

                    <h1 class="detalle-producto__titulo"><?php echo htmlspecialchars($producto['nombre']); ?></h1>

                    <!-- Promedio de estrellas calculado con las reseñas de la BD -->
                    <div class="detalle-producto__estrellas">
                        <?php echo estrellasHtml($producto['promedio_estrellas']); ?>
                        <span class="detalle-producto__estrellas-texto">
                            <?php echo escapar(resumenEstrellas($producto['promedio_estrellas'], $producto['total_resenias'])); ?>
                        </span>

                        <!-- Enlace a las reseñas: siempre visible, para que se
                             vea la flecha también cuando el producto todavía no
                             tiene opiniones. -->
                        <a class="detalle-producto__estrellas-enlace" href="#opiniones">
                            <?php if ((int) $producto['total_resenias'] > 0): ?>
                                Ver reseñas <span class="flecha-abajo" aria-hidden="true">↓</span>
                            <?php else: ?>
                                Escribir la primera reseña <span class="flecha-abajo" aria-hidden="true">↓</span>
                            <?php endif; ?>
                        </a>
                    </div>

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

        </section>



        <!-- ==========================
           RESEÑAS DEL PRODUCTO
           Estrellas + comentario; se listan todas las reseñas con el
           nombre y la foto de perfil de quien la escribió.
        ========================== -->

        <section class="dudas" id="opiniones">

            <div class="divisor-negro"></div>

            <div class="resenias-cabecera">
                <h2>Opiniones del Producto</h2>

                <div class="resenias-resumen">
                    <?php echo estrellasHtml($producto['promedio_estrellas']); ?>
                    <span class="resenias-resumen__nota">
                        <?php echo escapar(number_format(round((float) $producto['promedio_estrellas'], 1), 1, '.', '')); ?>
                    </span>
                    <span class="resenias-resumen__total">
                        <?php
                            $totalResenias = (int) $producto['total_resenias'];
                            echo $totalResenias === 1 ? '1 reseña' : $totalResenias . ' reseñas';
                        ?>
                    </span>
                </div>
            </div>

            <!-- ---------- Aviso del resultado al guardar ---------- -->
            <?php
                $resultadoResenia = isset($_GET['resenia']) ? (string) $_GET['resenia'] : '';
                $avisosResenia = array(
                    'ok'         => '¡Gracias! Tu reseña se guardó correctamente.',
                    'estrellas'  => 'Elegí una calificación de 1 a 5 estrellas.',
                    'comentario' => 'Escribí un comentario (máximo 1000 caracteres).',
                    'error'      => 'No se pudo guardar la reseña. Intentalo de nuevo.',
                );
            ?>

            <?php if (isset($avisosResenia[$resultadoResenia])): ?>
                <p class="aviso-resenia <?php echo $resultadoResenia === 'ok' ? 'aviso-resenia--ok' : 'aviso-resenia--error'; ?>" role="alert">
                    <?php echo escapar($avisosResenia[$resultadoResenia]); ?>
                </p>
            <?php endif; ?>

            <!-- ---------- Formulario de reseña ---------- -->
            <?php if (usuarioLogueado()): ?>

                <?php
                    $usuarioParaResenia = usuarioActual();
                    $calificacionActual = $reseniaDelUsuario ? (int) $reseniaDelUsuario['calificacion'] : 0;
                ?>

                <form
                    action="index.php?pagina=resenias"
                    method="POST"
                    class="form-resenia"
                    id="form-resenia">

                    <input type="hidden" name="id_producto" value="<?php echo (int) $producto['id_producto']; ?>">

                    <!-- Calificación: los radios se ocultan y se muestran como
                         estrellas (CSS). Funcionan sin JavaScript. -->
                    <fieldset class="selector-estrellas">
                        <legend>
                            <?php echo $reseniaDelUsuario ? 'Tu reseña' : 'Dejá tu reseña'; ?>
                        </legend>

                        <div class="selector-estrellas__grupo">
                            <?php for ($estrella = 5; $estrella >= 1; $estrella--): ?>
                                <input
                                    type="radio"
                                    name="calificacion"
                                    id="estrella-<?php echo $estrella; ?>"
                                    value="<?php echo $estrella; ?>"
                                    <?php echo $calificacionActual === $estrella ? 'checked' : ''; ?>
                                    required>
                                <label for="estrella-<?php echo $estrella; ?>" title="<?php echo $estrella; ?> estrella<?php echo $estrella === 1 ? '' : 's'; ?>">
                                    <?php echo $estrella; ?> ★
                                </label>
                            <?php endfor; ?>
                        </div>
                    </fieldset>

                    <!-- Foto y nombre del usuario que está opinando -->
                    <div class="form-resenia__autor">
                        <?php if (!empty($usuarioParaResenia['foto'])): ?>
                            <img
                                class="resenia__foto"
                                src="<?php echo escapar($usuarioParaResenia['foto']); ?>"
                                alt="Foto de perfil de <?php echo escapar($usuarioParaResenia['nombre']); ?>">
                        <?php else: ?>
                            <svg class="resenia__foto-icono" aria-hidden="true">
                                <use href="public/img/iconos.svg#icono-persona"></use>
                            </svg>
                        <?php endif; ?>
                        <span class="resenia__nombre"><?php echo escapar($usuarioParaResenia['nombre']); ?></span>
                    </div>

                    <textarea
                        name="comentario"
                        id="comentario-resenia"
                        class="form-resenia__comentario"
                        rows="3"
                        maxlength="1000"
                        placeholder="Contá tu experiencia con este producto..."
                        required><?php echo escapar($reseniaDelUsuario['comentario'] ?? ''); ?></textarea>

                    <button type="submit">
                        <?php echo $reseniaDelUsuario ? 'Actualizar' : 'Enviar'; ?>
                    </button>
                </form>

            <?php else: ?>

                <p class="resenia-invitacion">
                    Para dejar tu reseña tenés que
                    <a href="index.php?pagina=login">iniciar sesión</a>.
                </p>

            <?php endif; ?>

            <!-- ---------- Listado de reseñas de todos los usuarios ---------- -->
            <?php if (empty($listaDeResenias)): ?>

                <p class="resenia-vacia">Este producto todavía no tiene reseñas. ¡Sé el primero en opinar!</p>

            <?php else: ?>

                <details class="dudas-toggle" open>
                    <summary>Reseñas de otros Usuarios (<?php echo count($listaDeResenias); ?>)</summary>

                    <div class="dudas-contenido lista-resenias">

                        <?php foreach ($listaDeResenias as $resenia): ?>

                            <article class="resenia">

                                <header class="resenia__cabecera">

                                    <?php if (!empty($resenia['foto_usuario'])): ?>
                                        <img
                                            class="resenia__foto"
                                            src="<?php echo escapar($resenia['foto_usuario']); ?>"
                                            alt="Foto de perfil de <?php echo escapar($resenia['nombre_usuario']); ?>"
                                            loading="lazy">
                                    <?php else: ?>
                                        <svg class="resenia__foto-icono" aria-hidden="true">
                                            <use href="public/img/iconos.svg#icono-persona"></use>
                                        </svg>
                                    <?php endif; ?>

                                    <div class="resenia__autor">
                                        <span class="resenia__nombre"><?php echo escapar($resenia['nombre_usuario']); ?></span>
                                        <span class="resenia__fecha">
                                            <?php echo escapar(date('d/m/Y', strtotime($resenia['fecha']))); ?>
                                        </span>
                                    </div>

                                    <div class="resenia__calificacion">
                                        <?php echo estrellasHtml($resenia['calificacion']); ?>
                                    </div>

                                </header>

                                <p class="resenia__comentario">
                                    <?php echo nl2br(escapar($resenia['comentario'])); ?>
                                </p>

                            </article>

                        <?php endforeach; ?>

                    </div>
                </details>

            <?php endif; ?>

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

                <?php foreach ($listaDeProductos as $productoRelacionado): ?>
                    
                    <article class="tarjeta-producto">
                        <a
                            class="tarjeta-producto__enlace"
                            href="index.php?pagina=producto&id=<?php echo $productoRelacionado['id_producto']; ?>"
                            title="Ver detalle de <?php echo htmlspecialchars($productoRelacionado['nombre']); ?>">
                            <img
                                class="tarjeta-producto__imagen"
                                src="public/img/ImagenesProductos/<?php echo htmlspecialchars($productoRelacionado['imagen']); ?>"
                                alt="<?php echo htmlspecialchars($productoRelacionado['nombre']); ?>">
                        </a>
                        <span class="categoria-badge"><?php echo htmlspecialchars($productoRelacionado['nombre_categoria']); ?></span>
                        <h3><?php echo htmlspecialchars($productoRelacionado['nombre']); ?></h3>
                        <p class="precio">$<?php echo number_format($productoRelacionado['precio'], 2, ',', '.'); ?></p>
                        <div class="tarjeta-producto__estrellas">
                            <?php echo estrellasHtml($productoRelacionado['promedio_estrellas'] ?? 0); ?>
                            <span><?php echo escapar(resumenEstrellas($productoRelacionado['promedio_estrellas'] ?? 0, $productoRelacionado['total_resenias'] ?? 0)); ?></span>
                        </div>
                        <p class="stock">Stock: <?php echo (int) $productoRelacionado['stock']; ?></p>

                        <div class="tarjeta-producto__acciones">
                            <a
                                href="index.php?pagina=producto&id=<?php echo $productoRelacionado['id_producto']; ?>"
                                class="boton boton--secundario">
                                Ver detalles
                            </a>

                            <button
                                class="boton boton--principal boton-agregar-carrito"
                                data-id-producto="<?php echo $productoRelacionado['id_producto']; ?>"
                                data-nombre-producto="<?php echo htmlspecialchars($productoRelacionado['nombre']); ?>"
                                data-precio-producto="<?php echo $productoRelacionado['precio']; ?>"
                                data-stock-producto="<?php echo (int) $productoRelacionado['stock']; ?>"
                                <?php echo ($productoRelacionado['stock'] <= 0) ? 'disabled' : ''; ?>>
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
// carrusel.js pone las flechas en "Más de <categoría>" cuando hay
// más de 4 productos relacionados.
// (footer.php ya carga storage.js y login.js)
$scriptsExtra = ['public/js/catalogo.js', 'public/js/carrusel.js'];
require __DIR__ . '/footer.php';
?>