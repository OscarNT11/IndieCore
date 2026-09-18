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

        <srcipt src="../js/login.js"></script>