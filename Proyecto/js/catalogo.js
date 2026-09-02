const botonesAgregar = document.querySelectorAll(".btn-agregar-carrito");

botonesAgregar.forEach((boton) => {

    boton.addEventListener("click", () => {

        const tarjeta = boton.closest(".carta-producto");

        const producto = {
            nombre: tarjeta.querySelector("h3").textContent,
            precio: tarjeta.querySelector("p").textContent,
            imagen: tarjeta.querySelector("img").src,
            cantidad: 1
        };

        const carrito = obtenerCarrito();

        carrito.push(producto);

        guardarCarrito(carrito);

        actualizarContador();
    });
});