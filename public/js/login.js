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

formularioLogin.addEventListener("submit", (evento) => {

    evento.preventDefault();

    const contraseña = document.getElementById("ingresar-cont-login").value;

    if (contraseña.length > limite) {
        alert("La contraseña no puede superar los 20 caracteres");
    }

    const nombre = document.getElementById('ingresar-usua-login').value;

    if (nombre.length > limite) {
        alert("El nombre de usuario no puede superar los 20 caracteres");
    }


});

formularioRegistro.addEventListener("submit", (evento) => {

    evento.preventDefault();

    const contraseña = document.getElementById("ingresar-cont-registro").value;
    const confirmarContraseña = document.getElementById("ingresar-conf-registro").value;

    if (contraseña !== confirmarContraseña) {
        alert("Las contraseñas no coinciden");
    return;
    }

    if (contraseña.length > limite) {
        alert("La contraseña no puede superar los 20 caracteres");
    }

    if (contraseña.length <= minimo) {
        alert("La contraseña debe superar los 7 caracteres");
    }

    const nombre = document.getElementById('ingresar-usua-registro').value;

    if (nombre.length > limite) {
        alert("El nombre de usuario no puede superar los 20 caracteres");
    }


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