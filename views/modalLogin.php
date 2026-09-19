<!-- ==========================
    Pagina modular de inicio de sesión
    (se incluye desde cualquier vista con: require __DIR__ . '/modalLogin.php';)
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
                <input type="text" placeholder="Ingrese usuario..." class="espacio-texto" id="ingresar-usua-registro" required>
            </div>
            <div id="correo-electronico">
                <label for="ingresar-correo">Ingresar correo electronico</label>
                <input type="email" placeholder="Ingrese correo electronico..." class="espacio-texto" required>
            </div>
            <div class="contraseña">
                <label for="ingresar-contraseña">Ingresar contraseña</label>
                <input type="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-registro" required>
            </div>
            <div id="confirmar-contraseña">
                <label for="ingresar-confirmacion">Confirmar contraseña</label>
                <input type="password" placeholder="Confirmar contraseña..." class="espacio-texto" id="ingresar-conf-registro" required>
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
        </form>
    </div>
</div>
