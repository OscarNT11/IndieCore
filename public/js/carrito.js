
/* ==========================================================
   CARRITO DE COMPRAS
   Los productos y las cantidades se obtienen de SQL mediante
   CarritoController.php. Ya no se utiliza localStorage aquí.
   ========================================================== */

let carritoSQL = [];

/* ==========================================================
   SOLICITUDES AL CONTROLADOR
   ========================================================== */


async function solicitarCarrito(accion, datos = {}, metodo = "POST") {
    const opciones = {
        method: metodo
    };

    if (metodo === "POST") {
        opciones.headers = {
            "Content-Type": "application/x-www-form-urlencoded"
        };

        opciones.body = new URLSearchParams(datos);
    }

    try {
        const respuesta = await fetch(
            `index.php?pagina=carrito-api&accion=${encodeURIComponent(accion)}`,
            opciones
        );

        const datosRespuesta = await respuesta.json();

        if (!respuesta.ok) {
            return {
                ok: false,
                mensaje: datosRespuesta.mensaje ||
                    "El servidor no pudo procesar la solicitud."
            };
        }

        return datosRespuesta;

    } catch (error) {
        console.error("Error al comunicarse con el carrito:", error);

        return {
            ok: false,
            mensaje: "No se pudo conectar con el servidor. Intenta nuevamente."
        };
    }
}


/* ==========================================================
   AVISOS
   ========================================================== */

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

/* Escapa texto antes de insertarlo dentro del HTML generado. */
function escaparHtml(texto) {

    return String(texto ?? "").replace(/[&<>"']/g, (caracter) => {

        const caracteres = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        };

        return caracteres[caracter];
    });
}

/* ==========================================================
   MOSTRAR CARRITO
   ========================================================== */

function mostrarCarrito() {

    const lista = document.getElementById("contenedorCarrito");
    const estadoVacio = document.getElementById("carritoVacio");
    const resumen = document.getElementById("resumenCarrito");

    if (!lista) return;

    lista.innerHTML = "";

    if (carritoSQL.length === 0) {

        if (estadoVacio) estadoVacio.hidden = false;
        if (resumen) resumen.hidden = true;

        return;
    }

    if (estadoVacio) estadoVacio.hidden = true;
    if (resumen) resumen.hidden = false;

    carritoSQL.forEach((producto) => {

        const idProducto = Number(producto.id_producto);
        const cantidad = Number(producto.cantidad);
        const precio = normalizarPrecio(producto.precio);
        const stock = Math.max(0, Number(producto.stock) || 0);

        const atributoMax = `max="${stock}"`;

        const tarjeta = document.createElement("article");

        tarjeta.classList.add("producto-carrito");

        tarjeta.innerHTML = `
            <a class="producto-carrito__enlace"
               href="index.php?pagina=producto&id=${encodeURIComponent(idProducto)}"
               title="Ver detalle de ${escaparHtml(producto.nombre)}">

                <img class="producto-carrito__imagen"
                     src="public/img/ImagenesProductos/${escaparHtml(producto.imagen)}"
                     alt="${escaparHtml(producto.nombre)}">
            </a>

            <div class="producto-carrito__info">
                <h2>${escaparHtml(producto.nombre)}</h2>

                <p class="producto-carrito__unitario">
                    $${formatearPrecio(precio)} c/u
                </p>
            </div>

            <div class="producto-carrito__cantidad">

                <button type="button"
                        class="boton-cantidad"
                        data-accion="restar"
                        aria-label="Quitar una unidad">-</button>

                <input type="number"
                       class="entrada-cantidad"
                       value="${cantidad}"
                       min="1"
                       ${atributoMax}
                       aria-label="Cantidad de ${escaparHtml(producto.nombre)}">

                <button type="button"
                        class="boton-cantidad"
                        data-accion="sumar"
                        aria-label="Agregar una unidad"
                        ${cantidad >= stock ? "disabled" : ""}>+</button>
            </div>

            <h3 class="producto-carrito__subtotal">
                $${formatearPrecio(precio * cantidad)}
            </h3>

            <button type="button"
                    class="boton-eliminar"
                    aria-label="Eliminar del carrito">Quitar</button>
        `;

        // Botones para aumentar o disminuir la cantidad.
        tarjeta.querySelectorAll(".boton-cantidad").forEach((boton) => {

            boton.addEventListener("click", () => {

                const actual = Number(producto.cantidad);

                const nuevaCantidad =
                    boton.dataset.accion === "sumar"
                        ? actual + 1
                        : actual - 1;

                actualizarCantidad(idProducto, nuevaCantidad);
            });
        });

        // Cambio manual de cantidad.
        const entradaCantidad = tarjeta.querySelector(".entrada-cantidad");

        entradaCantidad.addEventListener("change", () => {

            actualizarCantidad(
                idProducto,
                Number(entradaCantidad.value)
            );
        });

        // Eliminar el producto.
        tarjeta.querySelector(".boton-eliminar")
            .addEventListener("click", () => {

                eliminarProducto(idProducto);
            });

        lista.appendChild(tarjeta);
    });
}

/* ==========================================================
   RESUMEN Y CONTADOR
   ========================================================== */

function actualizarResumen() {

    let cantidadTotal = 0;
    let total = 0;

    carritoSQL.forEach((producto) => {

        const cantidad = Number(producto.cantidad) || 0;
        const precio = normalizarPrecio(producto.precio);

        cantidadTotal += cantidad;
        total += precio * cantidad;
    });

    const cantidadProductos = document.getElementById("cantidad-carrito");
    const totalCarrito = document.getElementById("totalCarrito");
    const contador = document.getElementById("contador-carrito");

    if (cantidadProductos) {

        cantidadProductos.textContent =
            `Cantidad de productos (${cantidadTotal})`;
    }

    if (totalCarrito) {

        totalCarrito.textContent =
            `Total: $${formatearPrecio(total)}`;
    }

    if (contador) {

        contador.textContent = cantidadTotal;
    }
}

/* Actualiza toda la interfaz con el estado actual de SQL. */
function refrescarCarrito() {

    mostrarCarrito();
    actualizarResumen();
}

/* Consulta el carrito al entrar a la página. */

async function cargarCarrito() {
    const datos = await solicitarCarrito("listar", {}, "GET");

    if (!datos.ok || !Array.isArray(datos.carrito)) {
        mostrarAviso(
            datos.mensaje || "No se pudo cargar el carrito."
        );
        return;
    }

    carritoSQL = datos.carrito;
    refrescarCarrito();
}


/* ==========================================================
   MODIFICAR CANTIDADES
   ========================================================== */


async function actualizarCantidad(idProducto, nuevaCantidad) {
    if (!Number.isFinite(nuevaCantidad)) {
        mostrarCarrito();
        return;
    }

    if (nuevaCantidad <= 0) {
        await eliminarProducto(idProducto);
        return;
    }

    const producto = carritoSQL.find(
        (item) => Number(item.id_producto) === Number(idProducto)
    );

    if (!producto) return;

    const stock = Number(producto.stock);

    if (nuevaCantidad > stock) {
        mostrarAviso(`Solo hay ${stock} unidades disponibles.`);
        mostrarCarrito();
        return;
    }

    const datos = await solicitarCarrito("actualizar", {
        id_producto: idProducto,
        cantidad: nuevaCantidad
    });

    if (!datos.ok || !Array.isArray(datos.carrito)) {
        mostrarAviso(
            datos.mensaje || "No se pudo actualizar la cantidad."
        );

        // Restaura la cantidad que conserva el estado confirmado.
        mostrarCarrito();
        return;
    }

    carritoSQL = datos.carrito;
    refrescarCarrito();
}


/* ==========================================================
   ELIMINAR PRODUCTO
   ========================================================== */


async function eliminarProducto(idProducto) {
    const datos = await solicitarCarrito("eliminar", {
        id_producto: idProducto
    });

    if (!datos.ok || !Array.isArray(datos.carrito)) {
        mostrarAviso(
            datos.mensaje || "No se pudo eliminar el producto."
        );

        mostrarCarrito();
        return;
    }

    carritoSQL = datos.carrito;
    refrescarCarrito();

    mostrarAviso("Producto eliminado del carrito.");
}


/* ==========================================================
   VACIAR CARRITO
   ========================================================== */

const botonVaciar = document.getElementById("botonVaciarCarrito");

if (botonVaciar) {

    botonVaciar.addEventListener("click", async () => {

        if (!confirm("¿Seguro que quieres vaciar el carrito?")) return;

        const datos = await solicitarCarrito("vaciar");

        if (!datos.ok) {

            mostrarAviso("No se pudo vaciar el carrito.");

            return;
        }

        carritoSQL = Array.isArray(datos.carrito)
            ? datos.carrito
            : [];

        refrescarCarrito();

        mostrarAviso("Carrito vaciado.");
    });
}

/* ==========================================================
   FINALIZAR COMPRA
   ========================================================== */

const botonFinalizar = document.getElementById("botonFinalizarCompra");

if (botonFinalizar) {

    botonFinalizar.addEventListener("click", () => {

        if (carritoSQL.length === 0) {

            alert("Tu carrito está vacío.");

            return;
        }

        // El checkout obtiene los productos directamente desde SQL.
        window.location.href = "index.php?pagina=checkout";
    });
}

/* ==========================================================
   MENSAJES DEL PEDIDO
   ========================================================== */

const MENSAJES_DEL_PEDIDO = {

    error: "No se pudo registrar el pedido. Intenta nuevamente.",

    sesion: "Iniciá sesión para poder confirmar tu pedido.",

    vacio: "Algunos productos ya no tienen stock. Revisá el carrito."
};

function avisarResultadoDelPedido() {

    const resultado = new URLSearchParams(
        window.location.search
    ).get("pedido");

    const mensaje = MENSAJES_DEL_PEDIDO[resultado];

    if (!mensaje) return;

    alert(mensaje);

    if (resultado === "sesion") {

        const pantallaLogin = document.getElementById(
            "pantalla-transparente"
        );

        if (pantallaLogin) {

            pantallaLogin.style.display = "flex";
        }
    }

    const urlLimpia = new URL(window.location.href);

    urlLimpia.searchParams.delete("pedido");

    window.history.replaceState({}, "", urlLimpia);
}

/* ==========================================================
   INICIALIZACIÓN
   ========================================================== */

cargarCarrito();

avisarResultadoDelPedido();
