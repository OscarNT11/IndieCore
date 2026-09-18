
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

        <script src="public/js/storage.js"></script>
        <script src="public/js/login.js"></script>

        <?php
            if (isset($scriptExtra)) {
                echo '<script src="' . htmlspecialchars($scriptExtra) . '"></script>';
            }

            if (isset($scriptEnLinea)) {
                echo '<script>' . $scriptEnLinea . '</script>';
            }
            ?>
        
</body>
</html>
