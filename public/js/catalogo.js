/* ==========================================================
   BOTONES "AGREGAR AL CARRITO"
   Se usa en el catálogo, en "Lo nuevo" / "Destacados" del
   inicio y en el detalle de producto: en todas esas vistas el
   botón lleva la clase .boton-agregar-carrito y los datos del
   producto en atributos data-*.
   ========================================================== */

const botonesAgregar = document.querySelectorAll(".boton-agregar-carrito");


botonesAgregar.forEach((boton) => {

    boton.addEventListener("click", async () => {

        // Evita enviar varias solicitudes por clics repetidos.
        if (boton.disabled) return;

        const id = boton.dataset.idProducto;
        const nombre = boton.dataset.nombreProducto;
        const cantidad = leerCantidadDelBoton(boton);

        if (!id || cantidad < 1) {
            mostrarAviso("Los datos del producto no son válidos.");
            return;
        }

        boton.disabled = true;

        try {
            const respuesta = await fetch(
                "index.php?pagina=carrito-api&accion=agregar",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        id_producto: id,
                        cantidad: cantidad
                    })
                }
            );

            const datos = await respuesta.json();

            if (!respuesta.ok || !datos.ok) {
                mostrarAviso(
                    datos.mensaje || "No se pudo agregar el producto."
                );
                return;
            }

            if (!Array.isArray(datos.carrito)) {
                mostrarAviso("El servidor devolvió una respuesta inesperada.");
                return;
            }

            const contador = document.getElementById("contador-carrito");

            if (contador) {
                const cantidadTotal = datos.carrito.reduce(
                    (total, producto) =>
                        total + Number(producto.cantidad),
                    0
                );

                contador.textContent = cantidadTotal;
            }

            mostrarAviso(`${cantidad} x ${nombre} se agregó al carrito`);

        } catch (error) {
            console.error("Error al agregar el producto:", error);

            mostrarAviso(
                "No se pudo conectar con el servidor. Intenta nuevamente."
            );

        } finally {
            boton.disabled = false;
        }
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

    }, 2500);
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
