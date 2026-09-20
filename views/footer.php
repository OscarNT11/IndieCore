
</main>

<footer class="footer">

            <section id="redes">

                <div class="red-social">
                    <svg class="icono">
                    <use href="public/img/iconos.svg#icono-ubicacion"></use>
                    </svg>
                    <div class="redes-txt">
                        <h2>Nuestras Tiendas</h2>
                        <p>Encuentra una cercana de tu casa</p>
                    </div>
                </div>

                <div class="red-social">
                    <svg class="icono">
                    <use href="public/img/iconos.svg#icono-telefono"></use>
                    </svg>
                    <div class="redes-txt">
                        <h2>Llamanos al 095 756 103</h2>
                        <p>Atencion personal</p>
                    </div>
                </div>

                <div class="red-social">
                    <svg class="icono">
                    <use href="public/img/iconos.svg#icono-candado"></use>
                    </svg>
                    <div class="redes-txt">
                        <h2>Compra garantizada</h2>
                        <p>Te devolvemos tu dinero</p>
                    </div>
                </div>

            </section>

            <section id="promociones">
                <div class="promociones-img" id="promociones-1"></div>
                <div class="promociones-img" id="promociones-2"></div>
            </section>

        </footer>

        <?php
            // Se añade ?v=filemtime(...) a cada JS igual que se hace con los CSS
            // del header: si no, el navegador sigue usando la copia vieja
            // guardada en caché y los cambios no se ven al recargar.
            function etiquetaScript($rutaRelativa)
            {
                $rutaAbsoluta = __DIR__ . '/../' . $rutaRelativa;

                $version = is_file($rutaAbsoluta) ? filemtime($rutaAbsoluta) : time();

                echo '<script src="' . htmlspecialchars($rutaRelativa)
                    . '?v=' . $version . '"></script>' . PHP_EOL;
            }

            // Scripts que necesitan TODAS las páginas
            etiquetaScript('public/js/storage.js');
            etiquetaScript('public/js/login.js');

            // $scriptsExtra acepta uno o varios archivos JS propios de la vista,
            // siempre DESPUÉS de storage.js, que ya quedó cargado arriba.
            if (isset($scriptsExtra)) {

                foreach ((array) $scriptsExtra as $archivoScript) {
                    etiquetaScript($archivoScript);
                }
            }

            if (isset($scriptEnLinea)) {
                echo '<script>' . $scriptEnLinea . '</script>';
            }
            ?>
        
</body>
</html>
