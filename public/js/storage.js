/* ==========================================================
   UTILIDADES DEL CARRITO
   Este archivo se carga en todas las páginas.
   Contiene funciones para formatear precios y actualizar
   el contador del carrito mediante datos obtenidos desde SQL.
   ========================================================== */
   
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


async function actualizarContador() {
    const contador = document.getElementById("contador-carrito");

    if (!contador) return;

    contador.textContent = "0";

    try {
        const respuesta = await fetch(
            "index.php?pagina=carrito-api&accion=listar"
        );

        const datos = await respuesta.json();

        if (!respuesta.ok || !datos.ok || !Array.isArray(datos.carrito)) {
            console.error("No se pudo obtener el carrito:", datos.mensaje);
            return;
        }

        const cantidadTotal = datos.carrito.reduce(
            (total, producto) => total + Number(producto.cantidad),
            0
        );

        contador.textContent = cantidadTotal;

    } catch (error) {
        console.error("Error al actualizar el contador:", error);
    }
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