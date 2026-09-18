<?php require __DIR__ . '/header.php'; ?> 

            <!-- HERO - SECTION -->
            
            <section class="hero">
                <div class="hero-txt">
                    <h2>Tarjetas<br>gráficas</h2>
                    <a href="paginaListado.php">Ver más...</a>
                </div>
                <div class="hero-imgs">
                    <img src="public/img/grafica rtx 5090.png" alt="No" id="grafica-1">
                    <img src="public/img/GPU_ASRock_Radeon_RX7600_Challenger_OC_8Gb_1-600x600-Photoroom (1).png" alt="No" id="grafica-2">
                    <img src="public/img/D_NQ_NP_860832-MLU78186417488_082024-O-Photoroom (1).png" alt="No" id="grafica-3">
                </div>
            </section>

            <!-- CATEGORIAS -->
            
            <section class="categorias">

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Teclado.png" alt="Imagen de Teclado">
                    <h2>Teclados</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Monitor.png" alt="Imagen de Monitor">
                    <h2>Monitores</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Celular.png" alt="Imagen de Celular">
                    <h2>Celulares</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Consola.png" alt="Imagen de Consola">
                    <h2>Consolas</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Auriculares.png" alt="Imagen de Auriculares">
                    <h2>Auriculares</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Notebook.png" alt="Imagen de Notebook">
                    <h2>Notebooks</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Tarjetas graficas.png" alt="Imagen de Tarjeta Gráfica">
                    <h2>Tarjetas G.</h2>
                    </div>
                </a>

                <a href="paginaListado.php" class="categoria-link">
                    <div class="categorias-objeto">
                    <img src="public/img/Mause.png" alt="Imagen de Mouse">
                    <h2>Mouse</h2>
                    </div>
                </a>

                
            </section>

            <!-- LO NUEVO -->
            <section id="lo-nuevo">
                <?php if (empty($listaDeProductos)): ?>
                    <p class="mensaje-vacio">No se encontraron productos.</p>
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

                                <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                <p class="precio">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>

                                <button
                                    class="boton boton--principal boton-agregar-carrito"
                                    data-id-producto="<?php echo $producto['id_producto']; ?>"
                                    data-nombre-producto="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                    data-precio-producto="<?php echo $producto['precio']; ?>"
                                    <?php echo ($producto['stock'] <= 0) ? 'disabled' : ''; ?>>
                                    Agregar al carrito
                                </button>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </section>

            <!-- ==========================
            CATEGORIAS VARIADAS
            Seccion con 3 categorias principales
            e importantes que estan destacadas
            en la parte media de la pagina
            ========================== -->
            <section id="cat-variadas">

                <div id="cat-arriba">

                    <div id="cate-internos">
                        <div id="img-cat-internos">
                            <img src="public/img/Grafica-edit.png" alt="No se encontro la imagen">
                        </div>
                        <div id="cont-cat-internos">
                            <h2>Componentes
                                <br> internos</h2>
                            <button><a href="paginaListado.php">Entrar</a></button>
                        </div>
                    </div>

                    <div id="cate-perifericos">
                        <div id="cont-cat-perifericos">
                            <h2>Perifericos</h2>
                            <button><a href="paginaListado.php">Entrar</a></button>
                        </div>
                        <div id="img-cat-perifericos">
                            <img src="public/img/Mouse-edit.png" alt="No se encontro la imagen">
                        </div>
                    </div>

                </div>

                <div id="cate-larga">
                    <div id="img-cat-larga">
                        <img src="public/img/Celulares-edit.png" alt="No se encontro la imagen">
                    </div>
                    <div id="cont-cat-larga">
                        <h2>Otros
                            <br> dispositivos</h2>
                        <button><a href="paginaListado.php">Entrar</a></button>
                    </div>
                </div>

            </section>

            <!-- ==========================
            DESTACADOS
            Seccion de la pagina donde se muestran
            productos como en "LO NUEVO" pero en este caso,
            mostrando productos destacados, sean muy vendidos,
            populares o con buenas reseñas
            ========================== -->
            <section id="destacados">

                <h2>Destacados</h2>

                <div class="contenedor-destacado">
                    <?php if (empty($listaDeProductos)): ?>
                        <p class="mensaje-vacio">No se encontraron productos.</p>
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

                                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                                    <p class="precio">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></p>

                                    <button
                                        class="boton boton--principal boton-agregar-carrito"
                                        data-id-producto="<?php echo $producto['id_producto']; ?>"
                                        data-nombre-producto="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                        data-precio-producto="<?php echo $producto['precio']; ?>"
                                        <?php echo ($producto['stock'] <= 0) ? 'disabled' : ''; ?>>
                                        Agregar al carrito
                                    </button>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        <!-- ==========================
            Pagina modular de inicio de sesión
            ========================== -->

            <div id="pantalla-transparente">
                <div id="ventana-login">
                    <button class="cerrar-ventana">✕</button>
                    <form id="login">
                    <div class="nombre-usuario">
                        <label for="ingresar-nombre">Nombre de usuario</label>
                        <input type="text" placeholder="Ingrese usuario..." class="espacio-texto" id="ingresar-usua-login" required>
                    </div>
                    <div class="contraseña">
                        <label for="ingresar-contraseña">Contraseña</label>
                        <input type="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-login" required>
                    </div>
                    <div id="recordar">
                        <label for="recordar-cont" id="recordar">
                        <br>
                        <input type="checkbox" name="recordar-cont" id="recordar-cont">
                        <p>Recordar en el dispositivo</p>
                        </label>
                    </div>
                    <div class="boton">
                        <button type="submit" class="enviar" id="boton-inicioSesion">
                            Iniciar Sesion
                        </button>
                    </div>
                    <!-- ==========================
                    CUENTA CREADA
                    Un link que te lleva a registrarte por si
                    no tienes una cuenta creada
                    ========================== -->
                    <div class="cuenta-creada">
                        <p>No tienes una cuenta creada?</p>
                        <a href="" id="crear-cuenta">Crear cuenta</a>
                    </div>
                    </form>
                </div>
            </div>

            <!-- ==========================
            Pagina modular de registro
            ========================== -->

            <div id="pantalla-registro">
                <div id="ventana-registro">
                    <button class="cerrar-ventana">✕</button>
                    <form id="register">
                        <div class="nombre-usuario">
                            <label for="ingresar-nombre">Ingresar nombe de usuario</label>
                            <input type="text" placeholder="Ingrese usuario..." class="espacio-texto" id="ingresar-usua-registro"  required>
                        </div>
                        <div id="correo-electronico">
                            <label for="ingresar-correo">Ingresar correo electronico</label>
                            <input type="email" placeholder="Ingrese correo electronico..." class="espacio-texto" required>
                        </div>
                        <div class="contraseña">
                            <label for="ingresar-contraseña">Ingresar contraseña</label>
                            <input type="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-registro"  required>
                        </div>
                        <div id="confirmar-contraseña">
                            <label for="ingresar-confirmacion">Confirmar contraseña</label>
                            <input type="password" placeholder="Confirmar contraseña..." class="espacio-texto" id="ingresar-conf-registro"
                             required>
                        </div>
                        <div class="boton">
                        <button type="submit" class="enviar" id="boton-Registrarse">
                            Registrarse
                        </button>
                        </div>
                        <!-- ==========================
                        CUENTA CREADA
                        Un link que te lleva a iniciar sesion por si
                        ya tienes una cuenta creada en la pagina
                        ========================== -->
                        <div class="cuenta-creada">
                            <p>Ya tienes una cuenta?</p>
                            <a href="../html/paginainicio_inicio-sesion.html" id="volver-login">Inicia sesión</a>
                        </div>
                        </div>
                    </form>
                </div>
            </div>

        <!-- ===================================
        FOOTER Y SCRIPTS DE TODAS LAS PAGINAS
        =================================== -->

        <?php
// Esta vista necesita además carrito.js
// (footer.php ya carga storage.js y login.js)
$scriptExtra = 'public/js/carrito.js';
require __DIR__ . '/footer.php';
?>
