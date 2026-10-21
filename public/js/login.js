/*INICIO DE SESION Y REGISTRO */

const botonIngresar = document.querySelector(".btn-ingresar");

const pantallaTransparente = document.getElementById("pantalla-transparente");

const cerrarVentana = document.querySelectorAll(".cerrar-ventana");

const pantallaRegistro = document.getElementById("pantalla-registro");

const formularioRegistro = document.getElementById("register");

const formularioLogin = document.getElementById("login");

const limite = 20;

/* El campo de login acepta usuario O correo, y un correo puede llegar a
   superar los 20 caracteres del nombre de usuario; por eso usa su propio
   limite. */
const limiteCorreo = 255;

const minimo = 8;

/* ==========================================================
   MENSAJES DE ERROR
   Cada campo tiene debajo un <span class="mensaje-error"> vacío
   (ver modalLogin.php). Aquí se rellena y se marca el campo en
   rojo en vez de usar alert().
   ========================================================== */

function mostrarError(campo, texto) {

    if (!campo) return;

    campo.classList.add("campo-con-error");

    campo.setAttribute("aria-invalid", "true");

    const contenedor = campo.closest(".campo");

    const mensaje = contenedor?.querySelector(".mensaje-error");

    if (mensaje) mensaje.textContent = texto;
}

function limpiarError(campo) {

    if (!campo) return;

    campo.classList.remove("campo-con-error");

    campo.removeAttribute("aria-invalid");

    const contenedor = campo.closest(".campo");

    const mensaje = contenedor?.querySelector(".mensaje-error");

    if (mensaje) mensaje.textContent = "";
}

/* Vacía todos los mensajes de un formulario antes de validar de nuevo. */
function limpiarErrores(formulario) {

    formulario.querySelectorAll("input").forEach(limpiarError);
}

/* Devuelve true si el campo está vacío. */
function estaVacio(campo) {

    return campo.value.trim() === "";
}

/* Valida el formulario y, si todo está bien, LO ENVÍA al servidor
   (index.php?pagina=login) para que UsuarioController compruebe
   las credenciales contra la base de datos. */
formularioLogin.addEventListener("submit", (evento) => {

    limpiarErrores(formularioLogin);

    const campoNombre = document.getElementById("ingresar-usua-login");
    const campoContrasena = document.getElementById("ingresar-cont-login");

    let hayError = false;

    if (estaVacio(campoNombre)) {

        mostrarError(campoNombre, "Ingresa tu nombre de usuario o correo");

        hayError = true;

    } else if (campoNombre.value.length > limiteCorreo) {

        mostrarError(campoNombre, "El usuario o correo es demasiado largo");

        hayError = true;
    }

    if (estaVacio(campoContrasena)) {

        mostrarError(campoContrasena, "Ingresa tu contraseña");

        hayError = true;

    } else if (campoContrasena.value.length > limiteCorreo) {

        mostrarError(campoContrasena, "La contraseña es demasiado larga");

        hayError = true;
    }

    // Con cualquier error, el formulario no se envía al servidor.
    if (hayError) evento.preventDefault();
});

/* Igual que el login: valida y, si no hay errores, deja que el navegador
   envíe el formulario a index.php?pagina=registro, donde se crea la cuenta. */
formularioRegistro.addEventListener("submit", (evento) => {

    limpiarErrores(formularioRegistro);

    const campoNombre = document.getElementById("ingresar-usua-registro");
    const campoCorreo = document.getElementById("ingresar-correo-registro");
    const campoContrasena = document.getElementById("ingresar-cont-registro");
    const campoConfirmar = document.getElementById("ingresar-conf-registro");

    let hayError = false;

    // --- Nombre de usuario ---
    // Se rechazan los caracteres típicos de código/inyección (<, >, ", ', ;, &, etc.).
    // Solo se permiten letras (con tildes/ñ), números, espacios y _ - .
    const nombreValido = /^[\p{L}\p{N} _.-]+$/u.test(campoNombre.value.trim());

    if (estaVacio(campoNombre)) {

        mostrarError(campoNombre, "Ingresa un nombre de usuario");

        hayError = true;

    } else if (campoNombre.value.length > limite) {

        mostrarError(campoNombre, "El nombre de usuario no puede superar los 20 caracteres");

        hayError = true;

    } else if (!nombreValido) {

        mostrarError(campoNombre, "El nombre solo puede tener letras, números, espacios y _ - .");

        hayError = true;
    }

    // --- Correo electrónico ---
    const correoValido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campoCorreo.value.trim());

    if (estaVacio(campoCorreo)) {

        mostrarError(campoCorreo, "Ingresa tu correo electronico");

        hayError = true;

    } else if (!correoValido) {

        mostrarError(campoCorreo, "Ingresa un correo electronico valido");

        hayError = true;
    }

    // --- Contraseña: se comprueban TODOS los casos antes de seguir,
    //     si no, el primer mensaje tapa los demás. ---
    if (estaVacio(campoContrasena)) {

        mostrarError(campoContrasena, "Ingresa una contraseña");

        hayError = true;

    } else if (campoContrasena.value.length < minimo) {

        mostrarError(campoContrasena, `La contraseña debe tener al menos ${minimo} caracteres`);

        hayError = true;

    } else if (campoContrasena.value.length > limite) {

        mostrarError(campoContrasena, "La contraseña no puede superar los 20 caracteres");

        hayError = true;
    }

    // --- Confirmar contraseña ---
    if (estaVacio(campoConfirmar)) {

        mostrarError(campoConfirmar, "Confirma tu contraseña");

        hayError = true;

    } else if (campoContrasena.value !== campoConfirmar.value) {

        mostrarError(campoConfirmar, "Las contraseñas no coinciden");

        hayError = true;
    }

    // Con cualquier error, no se crea la cuenta.
    if (hayError) evento.preventDefault();
});

/* ==========================================================
   ABRIR EL MODAL SEGÚN LA URL
   El botón "Iniciar Sesion" del header lleva a index.php?pagina=login,
   así que al cargar esa página (o la de registro) se abre solo el modal
   correspondiente.
   ========================================================== */

const parametros = new URLSearchParams(window.location.search);

const paginaActual = parametros.get("pagina");

/* Cuando se acaba de iniciar sesión o registrarse, el controlador vuelve
   con ?login=ok / ?registro=ok. En ese caso NO se debe abrir el modal:
   hay que cerrarlo para devolver al usuario a la página en la que estaba. */
const acabaDeEntrar = parametros.get("login") === "ok"
    || parametros.get("registro") === "ok";

function abrirVentana(elemento) {

    if (elemento) elemento.style.display = "flex";
}

if (acabaDeEntrar) {

    cerrarVentanas();

} else if (paginaActual === "login") {

    abrirVentana(pantallaTransparente);

} else if (paginaActual === "registro") {

    abrirVentana(pantallaRegistro);
}

/* ==========================================================
   AVISO DE ÉXITO
   Tras registrarse o iniciar sesión, el controlador vuelve a esta
   página con ?registro=ok o ?login=ok: aquí se muestra un aviso.
   ========================================================== */

function mostrarAvisoSesion() {

    let texto = "";

    if (parametros.get("registro") === "ok") {

        texto = "¡Cuenta creada! Ya iniciaste sesión.";

    } else if (parametros.get("login") === "ok") {

        texto = "Sesión iniciada correctamente.";
    }

    if (texto === "") return;

    const aviso = document.createElement("div");

    aviso.className = "aviso-sesion";

    aviso.setAttribute("role", "status");

    aviso.textContent = texto;

    document.body.appendChild(aviso);

    setTimeout(() => aviso.classList.add("aviso-sesion--oculto"), 2500);

    setTimeout(() => aviso.remove(), 3000);
}

mostrarAvisoSesion();

if (botonIngresar) {

    botonIngresar.addEventListener("click", () => {

        // Si hay un usuario con la sesión iniciada, el header ya no muestra
        // este botón (muestra su nombre y "Cerrar sesión").
        abrirVentana(pantallaTransparente);
    });
}

/* ==========================================================
   CAMBIAR ENTRE LOGIN Y REGISTRO SIN SALIR DE LA PÁGINA
   Los enlaces "Crear cuenta" (#crear-cuenta) y "Inicia sesión"
   (#volver-login) cambian de ventana dentro de la misma página
   en lugar de navegar a otra URL.
   ========================================================== */

function abrirLogin() {

    cerrarVentanas();

    abrirVentana(pantallaTransparente);
}

function abrirRegistro() {

    cerrarVentanas();

    abrirVentana(pantallaRegistro);
}

function cerrarVentanas() {

    if (pantallaTransparente) pantallaTransparente.style.display = "none";

    if (pantallaRegistro) pantallaRegistro.style.display = "none";
}

// Enlace "Crear cuenta" (dentro de la ventana de login) → abre el registro
// sin recargar la página.
const enlaceCrearCuenta = document.getElementById("crear-cuenta");

if (enlaceCrearCuenta) {

    enlaceCrearCuenta.addEventListener("click", (evento) => {

        evento.preventDefault();

        abrirRegistro();
    });
}

const enlaceVolverLogin = document.getElementById("volver-login");

if (enlaceVolverLogin) {

    enlaceVolverLogin.addEventListener("click", (evento) => {

        evento.preventDefault();

        abrirLogin();
    });
}

cerrarVentana.forEach((boton) => {

    boton.addEventListener("click", cerrarVentanas);

});