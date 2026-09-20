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
                    min="1"
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

/* Cambia la cantidad de un producto; si queda en 0 o menos, lo elimina. */
function actualizarCantidad(indice, nuevaCantidad) {

    const carrito = obtenerCarrito();

    if (!carrito[indice]) return;

    if (!Number.isFinite(nuevaCantidad) || nuevaCantidad <= 0) {

        eliminarProducto(indice);

        return;
    }

    carrito[indice].cantidad = Math.floor(nuevaCantidad);

    guardarCarrito(carrito);

    refrescarCarrito();
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

        // El alta del pedido en la base de datos todavía no está
        // implementada (controllers/PedidoController.php está vacío).
        alert("¡Gracias por tu compra! Pronto nos comunicaremos contigo.");

        guardarCarrito([]);

        refrescarCarrito();
    });
}

/* Al entrar a la página dibujamos lo que ya había guardado. */
refrescarCarrito();
