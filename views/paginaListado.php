<?php require __DIR__ . '/header.php'; ?>

<section class="catalogo">
    <h2>Catalogos de Productos</h2>

    <form action="index.php" method="GET" class="filtros">
        <input type="hidden" name="pagina" value="catalogo">

        <label for="selectorCategoria">Filtrar por categoría:</label>
        <select name="categoria" id="selectorCategoria">
            <option value="">Todas las categorías</option>
            <?php foreach ($listaDeCategorias as $categoria): ?>
                <option
                    value="<?php echo $categoria['id_categoria']; ?>"
                    <?php echo ($idCategoriaSeleccionada == $categoria['id_categoria']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="boton">Filtrar</button>
    </form>

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

        <?php
// Esta vista necesita catalogo.js
// (footer.php ya carga storage.js y login.js)
$scriptExtra = 'public/js/catalogo.js';
require __DIR__ . '/footer.php';
?>
