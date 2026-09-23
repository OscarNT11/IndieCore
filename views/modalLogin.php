<!-- ==========================
    Pagina modular de inicio de sesión
    (se incluye desde cualquier vista con: require __DIR__ . '/modalLogin.php';)
    ========================== -->

<div id="pantalla-transparente">
    <div id="ventana-login">
        <button class="cerrar-ventana">✕</button>
        <form id="login" novalidate>
            <div class="campo nombre-usuario">
                <label for="ingresar-usua-login">Nombre de usuario</label>
                <input type="text" placeholder="Ingrese usuario..." class="espacio-texto" id="ingresar-usua-login" required>
                <span class="mensaje-error" data-error-de="ingresar-usua-login" aria-live="polite"></span>
            </div>
            <div class="campo contraseña">
                <label for="ingresar-cont-login">Contraseña</label>
                <input type="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-login" required>
                <span class="mensaje-error" data-error-de="ingresar-cont-login" aria-live="polite"></span>
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
        <form id="register" novalidate>
            <div class="campo nombre-usuario">
                <label for="ingresar-usua-registro">Ingresar nombre de usuario</label>
                <input type="text" placeholder="Ingrese usuario..." class="espacio-texto" id="ingresar-usua-registro" required>
                <span class="mensaje-error" data-error-de="ingresar-usua-registro" aria-live="polite"></span>
            </div>
            <div class="campo" id="correo-electronico">
                <label for="ingresar-correo-registro">Ingresar correo electronico</label>
                <input type="email" placeholder="Ingrese correo electronico..." class="espacio-texto" id="ingresar-correo-registro" required>
                <span class="mensaje-error" data-error-de="ingresar-correo-registro" aria-live="polite"></span>
            </div>
            <div class="campo contraseña">
                <label for="ingresar-cont-registro">Ingresar contraseña</label>
                <input type="password" placeholder="Ingrese su contraseña..." class="espacio-texto" id="ingresar-cont-registro" required>
                <span class="mensaje-error" data-error-de="ingresar-cont-registro" aria-live="polite"></span>
            </div>
            <div class="campo" id="confirmar-contraseña">
                <label for="ingresar-conf-registro">Confirmar contraseña</label>
                <input type="password" placeholder="Confirmar contraseña..." class="espacio-texto" id="ingresar-conf-registro" required>
                <span class="mensaje-error" data-error-de="ingresar-conf-registro" aria-live="polite"></span>
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
