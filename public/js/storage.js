/* ==========================================================
   ALMACENAMIENTO DEL CARRITO (localStorage)
   Este archivo se carga en TODAS las páginas (footer.php),
   por eso aquí viven las funciones que usan catalogo.js y
   carrito.js: obtenerCarrito, guardarCarrito y el contador
   del header.
   ========================================================== */

const CLAVE_CARRITO = "carrito";

/* Devuelve siempre un array, aunque el localStorage esté vacío,
   corrupto o guardado con otra forma (evita romper el resto de los
   scripts con un JSON.parse fallido). */
function obtenerCarrito() {

    try {

        const guardado = JSON.parse(localStorage.getItem(CLAVE_CARRITO));

        return Array.isArray(guardado) ? guardado : [];

    } catch (error) {

        return [];
    }
}

function guardarCarrito(carrito) {
    localStorage.setItem(CLAVE_CARRITO, JSON.stringify(carrito));
}

/* Normaliza un precio que puede llegar como número (4.99) o como
   texto ya formateado ("4.99" o "1.234,56"). Devuelve siempre un número. */
function normalizarPrecio(precio) {

    if (typeof precio === "number") {
        return isFinite(precio) ? precio : 0;
    }

    let texto = String(precio ?? "").replace(/[^\d.,-]/g, "").trim();

    if (texto === "") {
        return 0;
    }

    // Formato español: 1.234,56  ->  1234.56
    if (texto.includes(",")) {
        texto = texto.replace(/\./g, "").replace(",", ".");
    }

    const numero = Number(texto);

    return isFinite(numero) ? numero : 0;
}

/* Suma la cantidad de TODOS los productos del carrito y actualiza
   el contador del header (<span id="contador-carrito">). */
function actualizarContador() {

    const contador = document.getElementById("contador-carrito");

    if (!contador) return;

    const carrito = obtenerCarrito();

    const cantidadTotal = carrito.reduce((acumulado, producto) => {

        const cantidad = Number(producto.cantidad) || 0;

        return acumulado + cantidad;

    }, 0);

    contador.textContent = cantidadTotal;
}

/* Formatea un número al estilo usado en las vistas de PHP:
   number_format($precio, 2, ',', '.')  ->  1.234,56 */
function formatearPrecio(numero) {

    return Number(numero || 0).toLocaleString("es-UY", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/* Se ejecuta en cada página: deja el contador del header en su valor
   real apenas carga el documento. */
document.addEventListener("DOMContentLoaded", actualizarContador);