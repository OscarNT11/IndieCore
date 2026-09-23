/* ==========================================================
   PÁGINA DEL CARRITO
   Este script SOLO se carga en views/paginaCarrito.php.
   Las funciones obtenerCarrito / guardarCarrito / actualizarContador
   vienen de storage.js, que el footer carga en todas las páginas.
   ========================================================== */

/* Pinta el resumen de compra: cantidad de productos y total.
   Los elementos están en paginaCarrito.php. */
function actualizarResumen() {

    const carrito = obtenerCarrito();

    const cantidadProductos = document.getElementById("cantidad-carrito");
    const totalCarrito = document.getElementById("totalCarrito");

    let cantidadTotal = 0;
    let total = 0;

    carrito.forEach((producto) => {

        const cantidad = Number(producto.cantidad) || 0;

        cantidadTotal += cantidad;

        total += normalizarPrecio(producto.precio) * cantidad;
    });

    if (cantidadProductos) {
        cantidadProductos.textContent = `Cantidad de productos (${cantidadTotal})`;
    }

    if (totalCarrito) {
        totalCarrito.textContent = `Total: $${formatearPrecio(total)}`;
    }
}

/* Escapa el texto que viene del nombre del producto para que no
   pueda inyectar HTML dentro de la tarjeta. */
function escaparHtml(texto) {

    const contenedor = document.createElement("div");

    contenedor.textContent = String(texto ?? "");

    return contenedor.innerHTML;
}

/* Stock máximo que se puede pedir de un producto del carrito.
   Lo guarda catalogo.js en cada línea; si el carrito se llenó antes de
   este cambio (o el producto no tenía stock declarado), no hay tope. */
function stockDeProducto(producto) {

    const stock = Number(producto ? producto.stock : NaN);

    return Number.isFinite(stock) && stock > 0 ? stock : Infinity;
}

/* Dibuja en pantalla la lista de productos guardados en el carrito. */
function mostrarCarrito() {

    const carrito = obtenerCarrito();

    const lista = document.getElementById("contenedorCarrito");

    const estadoVacio = document.getElementById("carritoVacio");

    const resumen = document.getElementById("resumenCarrito");

    if (!lista) return;

    lista.innerHTML = "";

    // Carrito vacío: se muestra el mensaje y se oculta el resumen
    if (carrito.length === 0) {

        if (estadoVacio) estadoVacio.hidden = false;

        if (resumen) resumen.hidden = true;

        return;
    }

    if (estadoVacio) estadoVacio.hidden = true;

    if (resumen) resumen.hidden = false;

    carrito.forEach((producto, indice) => {

        const cantidad = Number(producto.cantidad) || 1;

        const precio = normalizarPrecio(producto.precio);

        const stockMaximo = stockDeProducto(producto);

        // El input del carrito también respeta el stock de la BD
        const atributoMax = Number.isFinite(stockMaximo)
            ? ` max="${stockMaximo}"`
            : "";

        const tarjeta = document.createElement("article");

        tarjeta.classList.add("producto-carrito");

        tarjeta.innerHTML = `
            <a class="producto-carrito__enlace"
               href="index.php?pagina=producto&id=${encodeURIComponent(producto.id)}"
               title="Ver detalle de ${escaparHtml(producto.nombre)}">
                <img class="producto-carrito__imagen"
                     src="${escaparHtml(producto.imagen)}"
                     alt="${escaparHtml(producto.nombre)}">
            </a>

            <div class="producto-carrito__info">
                <h2>${escaparHtml(producto.nombre)}</h2>

                <p class="producto-carrito__unitario">
                    $${formatearPrecio(precio)} c/u
                </p>
            </div>

            <div class="producto-carrito__cantidad">
                <button type="button" class="boton-cantidad" data-accion="restar" aria-label="Quitar una unidad">-</button>

                <input
                    type="number"
                    class="entrada-cantidad"
                    value="${cantidad}"
                    min="1"${atributoMax}
                    aria-label="Cantidad de ${escaparHtml(producto.nombre)}">

                <button type="button" class="boton-cantidad" data-accion="sumar" aria-label="Agregar una unidad">+</button>
            </div>

            <h3 class="producto-carrito__subtotal">$${formatearPrecio(precio * cantidad)}</h3>

            <button type="button" class="boton-eliminar" aria-label="Eliminar del carrito">Quitar</button>
        `;

        // --- Cambiar la cantidad con los botones - / + ---
        tarjeta.querySelectorAll(".boton-cantidad").forEach((boton) => {

            boton.addEventListener("click", () => {

                const actual = Number(carrito[indice].cantidad) || 1;

                const nueva = boton.dataset.accion === "sumar" ? actual + 1 : actual - 1;

                actualizarCantidad(indice, nueva);
            });
        });

        // El botón "+" se bloquea al llegar al stock disponible
        const botonSumar = tarjeta.querySelector('[data-accion="sumar"]');

        if (botonSumar) botonSumar.disabled = cantidad >= stockMaximo;

        // --- Cambiar la cantidad escribiendo en el input ---
        const entradaCantidad = tarjeta.querySelector(".entrada-cantidad");

        entradaCantidad.addEventListener("change", () => {

            actualizarCantidad(indice, Number(entradaCantidad.value));
        });

        // --- Quitar el producto del carrito ---
        tarjeta.querySelector(".boton-eliminar").addEventListener("click", () => {

            eliminarProducto(indice);
        });

        lista.appendChild(tarjeta);
    });
}

/* Cambia la cantidad de un producto; si queda en 0 o menos, lo elimina.
   La cantidad nunca supera el stock que el producto traía de la BD. */
function actualizarCantidad(indice, nuevaCantidad) {

    const carrito = obtenerCarrito();

    if (!carrito[indice]) return;

    if (!Number.isFinite(nuevaCantidad) || nuevaCantidad <= 0) {

        eliminarProducto(indice);

        return;
    }

    const stockMaximo = stockDeProducto(carrito[indice]);

    // Se pedían más unidades de las que hay: se avisa al usuario
    // y se deja la cantidad en el máximo disponible.
    if (nuevaCantidad > stockMaximo) {

        mostrarAviso(`Solo quedan ${stockMaximo} unidades de ${carrito[indice].nombre}`);
    }

    carrito[indice].cantidad = Math.min(Math.floor(nuevaCantidad), stockMaximo);

    guardarCarrito(carrito);

    refrescarCarrito();
}

/* Aviso flotante (misma mecánica que en catalogo.js, pero catalogo.js
   solo se carga en el catálogo, el inicio y el detalle). */
function mostrarAviso(mensaje) {

    let aviso = document.getElementById("aviso-carrito");

    if (!aviso) {

        aviso = document.createElement("div");

        aviso.id = "aviso-carrito";

        document.body.appendChild(aviso);
    }

    aviso.textContent = mensaje;

    aviso.classList.add("aviso-carrito--visible");

    clearTimeout(aviso.temporizador);

    aviso.temporizador = setTimeout(() => {

        aviso.classList.remove("aviso-carrito--visible");

    }, 2000);
}

/* Elimina un producto puntual de la lista. */
function eliminarProducto(indice) {

    const carrito = obtenerCarrito();

    carrito.splice(indice, 1);

    guardarCarrito(carrito);

    refrescarCarrito();
}

/* Vuelve a dibujar todo: lista, resumen y contador del header. */
function refrescarCarrito() {

    mostrarCarrito();

    actualizarResumen();

    actualizarContador();
}

/* --- Botón "Vaciar carrito" --- */
const botonVaciar = document.getElementById("botonVaciarCarrito");

if (botonVaciar) {

    botonVaciar.addEventListener("click", () => {

        if (!confirm("¿Seguro que quieres vaciar el carrito?")) return;

        guardarCarrito([]);

        refrescarCarrito();
    });
}

/* --- Botón "Finalizar compra" --- */
const botonFinalizar = document.getElementById("botonFinalizarCompra");

if (botonFinalizar) {

    botonFinalizar.addEventListener("click", () => {

        if (obtenerCarrito().length === 0) {

            alert("Tu carrito está vacío.");

            return;
        }

        // Se envía el carrito al servidor (index.php?pagina=confirmar-pedido)
        // mediante un formulario oculto, y PedidoController lo guarda en la BD.
        const carrito = obtenerCarrito();

        const formulario = document.createElement("form");

        formulario.method = "POST";

        formulario.action = "index.php?pagina=confirmar-pedido";

        const campoCarrito = document.createElement("input");

        campoCarrito.type = "hidden";

        campoCarrito.name = "carritoJson";

        campoCarrito.value = JSON.stringify(carrito);

        formulario.appendChild(campoCarrito);

        document.body.appendChild(formulario);

        formulario.submit();
    });
}

/* Al entrar a la página dibujamos lo que ya había guardado. */
refrescarCarrito();

/* --- Resultado del pedido ---
   Solo se llega acá con ?pedido=error: el caso correcto ya no pasa por el
   carrito, se muestra views/pedido.php, que vacía el carrito por su cuenta. */
function avisarResultadoDelPedido() {

    const resultado = new URLSearchParams(window.location.search).get("pedido");

    if (resultado !== "error") return;

    alert("No se pudo registrar el pedido. Intenta nuevamente.");

    // Se quita el parámetro de la URL para que un refresco no repita el aviso.
    const urlLimpia = window.location.pathname + window.location.search
        .replace(/([?&])pedido=error&?/, "$1")
        .replace(/[?&]$/, "");

    window.history.replaceState({}, "", urlLimpia);
}

avisarResultadoDelPedido();
