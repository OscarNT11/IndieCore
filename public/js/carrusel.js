/* ==========================================================
   CARRUSEL DE PRODUCTOS (flechas izquierda / derecha)

/* Cuántas tarjetas se ven a la vez. Debe coincidir con el
   flex-basis del CSS (--carrusel-por-vista). */
const PRODUCTOS_POR_VISTA = 4;

/* Cada cuánto se desplaza un clic. Las flechas se deshabilitan
   en los extremos, por eso el paso puede ser de a 4 de una:
   no hay riesgo de quedar "trabado" en un punto intermedio. */
const PASO_DESPLAZAMIENTO = PRODUCTOS_POR_VISTA;

/* Cuántos píxeles se consideran "ya está en el borde".
   scrollLeft es fraccionario en pantallas con escalado (125%,
   150%) y comparar contra 0 exacto deja flechas activas de más. */
const MARGEN_BORDE = 2;

/* Envuelve cada grilla en un bloque con flechas, salvo que ya
   venga envuelta o que tenga 4 productos o menos (en ese caso
   no hace falta carrusel y se deja la grilla tal cual). */
function prepararCarruseles() {

    const grillas = document.querySelectorAll(".grilla-productos");

    grillas.forEach((grilla) => {

        const cantidad = grilla.children.length;

        // 4 o menos: entran todos, no se agregan flechas.
        if (cantidad <= PRODUCTOS_POR_VISTA) return;

        // Si la vista ya trae el envoltorio, se respeta.
        if (grilla.parentElement && grilla.parentElement.classList.contains("carrusel")) {
            conectarFlechas(grilla.parentElement, grilla);
            return;
        }

        const carrusel = document.createElement("div");

        carrusel.classList.add("carrusel");

        // Se inserta en el mismo lugar donde estaba la grilla
        grilla.parentNode.insertBefore(carrusel, grilla);

        carrusel.appendChild(grilla);

        const botonIzquierda = crearFlecha("izquierda");
        const botonDerecha = crearFlecha("derecha");

        carrusel.insertBefore(botonIzquierda, grilla);
        carrusel.appendChild(botonDerecha);

        conectarFlechas(carrusel, grilla);
    });
}

/* Crea una flecha con su símbolo y su etiqueta accesible. */
function crearFlecha(lado) {

    const boton = document.createElement("button");

    boton.type = "button";

    boton.classList.add("carrusel__flecha", `carrusel__flecha--${lado}`);

    boton.setAttribute("aria-label", lado === "izquierda"
        ? "Ver productos anteriores"
        : "Ver más productos");

    boton.textContent = lado === "izquierda" ? "‹" : "›";

    return boton;
}

/* Conecta las flechas de un carrusel con su fila de tarjetas. */
function conectarFlechas(carrusel, grilla) {

    const botonIzquierda = carrusel.querySelector(".carrusel__flecha--izquierda");
    const botonDerecha = carrusel.querySelector(".carrusel__flecha--derecha");

    if (!botonIzquierda || !botonDerecha) return;

    /* Cuánto se puede desplazar la fila en total. */
    function desplazamientoMaximo() {

        return grilla.scrollWidth - grilla.clientWidth;
    }

    /* Desplaza la fila una tarjeta "hacia adelante" (1) o atrás (-1). */
    function desplazar(direccion) {

        const anchoTarjeta = anchoDeUnaTarjeta(grilla);

        grilla.scrollBy({
            left: direccion * anchoTarjeta * PASO_DESPLAZAMIENTO,
            behavior: "smooth"
        });
    }

    /* Habilita una flecha solo si hay hacia dónde desplazarse. */
    function refrescarFlechas() {

        const maximo = desplazamientoMaximo();

        // La fila entra completa: no hay nada que desplazar
        carrusel.classList.toggle("carrusel--completo", maximo <= MARGEN_BORDE);

        botonIzquierda.disabled = grilla.scrollLeft <= MARGEN_BORDE;

        botonDerecha.disabled = grilla.scrollLeft >= maximo - MARGEN_BORDE;
    }

    botonIzquierda.addEventListener("click", () => desplazar(-1));

    botonDerecha.addEventListener("click", () => desplazar(1));

    grilla.addEventListener("scroll", refrescarFlechas, { passive: true });

    // Al cambiar el tamaño de la ventana cambia cuánto entra
    window.addEventListener("resize", refrescarFlechas);

    // Estado inicial
    refrescarFlechas();
}

/* Ancho de una tarjeta más el hueco entre tarjetas (gap).
   Se mide sobre la primera tarjeta real, así el paso sigue siendo
   correcto si cambian tamaños, gap o el número por vista. */
function anchoDeUnaTarjeta(grilla) {

    const primera = grilla.firstElementChild;

    if (!primera) return grilla.clientWidth;

    const estilos = getComputedStyle(grilla);

    const hueco = parseFloat(estilos.columnGap) || parseFloat(estilos.gap) || 0;

    return primera.getBoundingClientRect().width + hueco;
}

document.addEventListener("DOMContentLoaded", prepararCarruseles);
