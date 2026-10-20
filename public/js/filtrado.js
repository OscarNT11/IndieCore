const botonFiltro = document.querySelector(".btn-filtro");
const filtrado = document.querySelector(".catalogo-categorias");
const footer = document.querySelector(".footer");

botonFiltro.addEventListener("click", () => {
    filtrado.classList.toggle("ocultar-filtro");
    filtrado.classList.toggle("mostrar-filtrado");
});


// Verifica que el DOM ha cargado correctamente
document.addEventListener("DOMContentLoaded", () => {

    // Que no sean null
    if (botonFiltro && footer) {

        // Cuando se scrolea
        window.addEventListener("scroll", () => {
            const rectFooter = footer.getBoundingClientRect();
            const alturaVentana = window.innerHeight;

            // Si el borde superior del footer ya entró en la pantalla visible
            if (rectFooter.top < alturaVentana) {
                // Calculamos cuantos pixeles ha entrado el footer en la pantalla
                const espacioOcupadoPorFooter = alturaVentana - rectFooter.top;
                
                // Sumamos el bottom original (2rem = 32px) mas lo que sube el footer
                // para que el boton se frene exactamente en el borde superior del footer
                botonFiltro.style.bottom = `${espacioOcupadoPorFooter + 20}px`; 
            } else {
                // Cuando esta arriba, vuelve a su posicion fija normal de 2rem (32px aprox)
                botonFiltro.style.bottom = "2rem";
            }
        });
    }
});