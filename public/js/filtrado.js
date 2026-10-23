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

// PARA MARCAS
document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('buscador-marca');
    if (!buscador) return;

    const labelsMarcas = document.querySelectorAll('.marcas-container .checkbox-label');
    const LIMITE_INICIAL = 5;

    // Función para mostrar solo las primeras 5 marcas por defecto
    const aplicarEstadoInicial = () => {
        labelsMarcas.forEach((label, index) => {
            const input = label.querySelector('input[type="checkbox"]');
            const estaSeleccionado = input && input.checked;
            if (index < LIMITE_INICIAL || estaSeleccionado) {
                label.classList.remove("oculto");
            } else {
                label.classList.add("oculto");
            }
        });
    };

    // Aplicar el límite al cargar la página
    aplicarEstadoInicial();

    buscador.addEventListener('input', (e) => {
        const textoBusqueda = e.target.value.toLowerCase().trim();

        // Si el buscador está vacío, volvemos a mostrar solo las primeras 5
        if (textoBusqueda === '') {
            aplicarEstadoInicial();
            return;
        }

        // Si el usuario escribe, filtramos entre todas las marcas
        labelsMarcas.forEach(label => {
            const nombreMarca = label.getAttribute('data-nombre');
            
            // Usamos includes (puedes usar startsWith si prefieres que coincida solo al empezar)
            if (nombreMarca.includes(textoBusqueda)) {
                label.classList.remove("oculto");
            } else {
                label.classList.add("oculto");
            }
        });
    });
});

// Validaciones para precios e input del filtrado
document.addEventListener('DOMContentLoaded', () => {
    // 1. GESTIÓN DEL BUSCADOR DE MARCAS Y LÍMITE
    const buscador = document.getElementById('buscador-marca');
    
    if (buscador) {
        // Nos aseguramos de que el buscador NO tenga el atributo 'name' 
        // para que no aparezca en la URL como ?buscador-marca=... al enviar el formulario GET
        buscador.removeAttribute('name');
    }

    // 2. VALIDACIÓN DEL FORMULARIO DE PRECIOS
    const formFiltros = document.querySelector('#form-filtros'); // Ajusta el selector según tu HTML del form
    if (formFiltros) {
        formFiltros.addEventListener('submit', (e) => {
            const inputMin = formFiltros.querySelector('input[name="precio_min"]');
            const inputMax = formFiltros.querySelector('input[name="precio_max"]');

            if (inputMin && inputMax) {
                let min = parseFloat(inputMin.value) || 0;
                let max = parseFloat(inputMax.value) || 0;

                // Evitar números negativos
                if (min < 0) inputMin.value = 0;
                if (max < 0) inputMax.value = 0;

                // Si puso un mínimo mayor que el máximo, los invertimos o prevenimos
                if (min > 0 && max > 0 && min > max) {
                    e.preventDefault();
                    alert('El precio mínimo no puede ser mayor que el precio máximo.');
                    return;
                }
            }
        });
    }
});