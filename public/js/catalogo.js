/* ==========================================================
   BOTONES "AGREGAR AL CARRITO"
   Se usa en el catálogo, en "Lo nuevo" / "Destacados" del
   inicio y en el detalle de producto: en todas esas vistas el
   botón lleva la clase .boton-agregar-carrito y los datos del
   producto en atributos data-*.
   ========================================================== */

const botonesAgregar = document.querySelectorAll(".boton-agregar-carrito");

botonesAgregar.forEach((boton) => {

    boton.addEventListener("click", () => {

        const id = boton.dataset.idProducto;

        const nombre = boton.dataset.nombreProducto;

        const precio = normalizarPrecio(boton.dataset.precioProducto);

        // Stock real del producto (lo manda la vista desde la BD).
        // Sin atributo o sin número => sin límite conocido.
        const stockDeclarado = Number(boton.dataset.stockProducto);

        const stock = Number.isFinite(stockDeclarado) ? stockDeclarado : Infinity;

        // Cantidad a agregar: en el detalle la elige el usuario con el
        // contador; en las tarjetas del catálogo es siempre 1.
        const cantidadPedida = leerCantidadDelBoton(boton);

        // La imagen está dentro de la misma tarjeta que el botón,
        // o en el bloque de imagen del detalle de producto.
        const contenedorImagen = boton.closest(".tarjeta-producto")
            || document.querySelector(".detalle-producto__imagen");

        const imagen = contenedorImagen ? contenedorImagen.querySelector("img") : null;

        const carrito = obtenerCarrito();

        // Si el producto ya está en el carrito se suma la cantidad
        // pedida en vez de duplicar la fila.
        const productoExistente = carrito.find((producto) => {

            return String(producto.id) === String(id);
        });

        const cantidadActual = productoExistente ? Number(productoExistente.cantidad) || 0 : 0;

        // No se puede pasar del stock disponible
        const cantidadFinal = Math.min(cantidadActual + cantidadPedida, stock);

        const agregadas = cantidadFinal - cantidadActual;

        if (agregadas <= 0) {

            mostrarAviso(`No hay más unidades de ${nombre} disponibles`);

            return;
        }

        if (productoExistente) {

            productoExistente.cantidad = cantidadFinal;

            // Se refresca el stock: pudo cambiar desde la última visita
            productoExistente.stock = stock;

        } else {

            carrito.push({
                id: id,
                nombre: nombre,
                precio: precio,
                imagen: imagen ? imagen.getAttribute("src") : "",
                cantidad: cantidadFinal,
                // Se guarda para que carrito.js pueda aplicar el mismo
                // tope al editar la cantidad desde la página del carrito.
                stock: stock
            });
        }

        guardarCarrito(carrito);

        actualizarContador();

        mostrarAviso(`${agregadas} x ${nombre} se agregó al carrito`);
    });
});

/* Devuelve cuántas unidades pide el usuario al pulsar el botón.
   Si el botón apunta a un contador (data-cantidad-origen), se lee de ahí;
   si no, es 1 (el caso de las tarjetas del catálogo y del inicio). */
function leerCantidadDelBoton(boton) {

    const idContador = boton.dataset.cantidadOrigen;

    if (!idContador) return 1;

    const entrada = document.getElementById(idContador);

    if (!entrada) return 1;

    const valor = Number(entrada.value);

    if (!Number.isFinite(valor) || valor < 1) return 1;

    return Math.floor(valor);
}

/* Aviso simple para que el usuario vea que el clic funcionó. */
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

/* ==========================================================
   CONTADOR DE CANTIDAD (solo en la página de detalle)
   Los botones - / + y el input respetan el stock máximo que
   la vista puso en el atributo max del input.
   ========================================================== */

const entradaDetalle = document.getElementById("cantidad-detalle");

if (entradaDetalle) {

    const botonRestar = document.getElementById("botonRestarCantidad");
    const botonSumar = document.getElementById("botonSumarCantidad");
    const avisoStock = document.getElementById("avisoStock");

    // El max lo genera PHP con el stock real; si no viniera, no hay tope
    const stockMaximo = Number(entradaDetalle.max) || Infinity;

    /* Deja el valor dentro de [1, stockMaximo] y sincroniza los botones. */
    function ajustarCantidad(valor) {

        let cantidad = Number(valor);

        if (!Number.isFinite(cantidad) || cantidad < 1) cantidad = 1;

        if (cantidad > stockMaximo) {

            cantidad = stockMaximo;

            if (avisoStock) avisoStock.hidden = false;

        } else if (avisoStock) {

            avisoStock.hidden = true;
        }

        entradaDetalle.value = Math.floor(cantidad);

        // Se desactiva el botón cuando ya no se puede seguir
        if (botonRestar) botonRestar.disabled = cantidad <= 1;
        if (botonSumar) botonSumar.disabled = cantidad >= stockMaximo;
    }

    if (botonRestar) {

        botonRestar.addEventListener("click", () => {

            ajustarCantidad(Number(entradaDetalle.value) - 1);
        });
    }

    if (botonSumar) {

        botonSumar.addEventListener("click", () => {

            ajustarCantidad(Number(entradaDetalle.value) + 1);
        });
    }

    // Si escribe a mano, se corrige al salir del campo
    entradaDetalle.addEventListener("change", () => {

        ajustarCantidad(entradaDetalle.value);
    });

    // Estado inicial correcto al cargar la página
    ajustarCantidad(entradaDetalle.value);
}
