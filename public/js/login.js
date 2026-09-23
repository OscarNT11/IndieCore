/*INICIO DE SESION Y REGISTRO */

const botonIngresar = document.querySelector(".btn-ingresar");

const botonIngreso = document.getElementById("volver-login");

const pantallaTransparente = document.getElementById("pantalla-transparente");

const cerrarVentana = document.querySelectorAll(".cerrar-ventana");

const botonRegistro = document.getElementById("crear-cuenta");

const pantallaRegistro = document.getElementById("pantalla-registro");

const formularioRegistro = document.getElementById("register");

const formularioLogin = document.getElementById("login");

const limite = 20;

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

formularioLogin.addEventListener("submit", (evento) => {

    evento.preventDefault();

    limpiarErrores(formularioLogin);

    const campoNombre = document.getElementById("ingresar-usua-login");
    const campoContrasena = document.getElementById("ingresar-cont-login");

    let hayError = false;

    if (estaVacio(campoNombre)) {

        mostrarError(campoNombre, "Ingresa tu nombre de usuario");

        hayError = true;

    } else if (campoNombre.value.length > limite) {

        mostrarError(campoNombre, "El nombre de usuario no puede superar los 20 caracteres");

        hayError = true;
    }

    if (estaVacio(campoContrasena)) {

        mostrarError(campoContrasena, "Ingresa tu contraseña");

        hayError = true;

    } else if (campoContrasena.value.length > limite) {

        mostrarError(campoContrasena, "La contraseña no puede superar los 20 caracteres");

        hayError = true;
    }

    // Con cualquier error, el formulario no se envía.
    if (hayError) return;
});

formularioRegistro.addEventListener("submit", (evento) => {

    evento.preventDefault();

    limpiarErrores(formularioRegistro);

    const campoNombre = document.getElementById("ingresar-usua-registro");
    const campoCorreo = document.getElementById("ingresar-correo-registro");
    const campoContrasena = document.getElementById("ingresar-cont-registro");
    const campoConfirmar = document.getElementById("ingresar-conf-registro");

    let hayError = false;

    // --- Nombre de usuario ---
    if (estaVacio(campoNombre)) {

        mostrarError(campoNombre, "Ingresa un nombre de usuario");

        hayError = true;

    } else if (campoNombre.value.length > limite) {

        mostrarError(campoNombre, "El nombre de usuario no puede superar los 20 caracteres");

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
    if (hayError) return;
});

botonRegistro.addEventListener("click", (evento) => {

    evento.preventDefault();

    pantallaRegistro.style.display = "flex";

});

botonIngreso.addEventListener("click", (evento) => {

    evento.preventDefault();

    pantallaRegistro.style.display = "none";

    pantallaTransparente.style.display = "flex";

});

botonIngresar.addEventListener("click", () => {

    pantallaTransparente.style.display = "flex";

});

cerrarVentana.forEach((boton) => {

    boton.addEventListener("click", () => {

        pantallaTransparente.style.display = "none";
        pantallaRegistro.style.display = "none";

    });

});