function actualizarContador() {

    const carrito = obtenerCarrito();

    const contador = document.getElementById("contador-carrito");

    if (contador) {

        let cantidadTotal = 0;

        carrito.forEach((producto) => {
            cantidadTotal += Number(producto.cantidad) || 1;
        });

        contador.textContent = cantidadTotal;
    }
}

actualizarContador();

function mostrarCarrito() {

    const carrito = obtenerCarrito();

    const lista = document.getElementById("lista-carrito");

    if (!lista) return;

    lista.innerHTML = "";

    carrito.forEach((producto, indice) => {

        const tarjeta = document.createElement("section");

        tarjeta.classList.add("producto");

        const cantidad = Number(producto.cantidad) || 1;

        tarjeta.innerHTML = `
            <img src="${producto.imagen}" alt="">

            <div>
                <h2>${producto.nombre}</h2>

                <label>Cantidad:</label>

                <input 
                    type="number" 
                    value="${cantidad}" 
                    min="0"
                >
            </div>

            <h3 class="producto-precio">${producto.precio}</h3>
        `;

        const inputCantidad = tarjeta.querySelector("input");

        inputCantidad.addEventListener("change", () => {

            const nuevaCantidad = Number(inputCantidad.value);

            if (nuevaCantidad <= 0) {

                carrito.splice(indice, 1);

            } else {

                carrito[indice].cantidad = nuevaCantidad;
            }

            guardarCarrito(carrito);

            actualizarContador();

            mostrarCarrito();
        });

        lista.appendChild(tarjeta);
    });
}

mostrarCarrito();


