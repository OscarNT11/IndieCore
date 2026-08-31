const botonIngresar = document.querySelector(".btn-ingresar");

const botonRegistro = document.getElementById("crear-cuenta");

const pantallaTransparente = document.getElementById("pantalla-transparente");

const cerrarLogin = document.getElementById("cerrar-login");

botonIngresar.addEventListener("click", () => {

    pantallaTransparente.style.display = "flex";

});

cerrarLogin.addEventListener("click", () => {

    pantallaTransparente.style.display = "none";

});

botonRegistro.addEventListener("click", () => {

    pantallaTransparente.style.display = "flex";

});