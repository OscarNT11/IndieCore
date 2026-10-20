/* ==========================================================
   FACTURA DEL PEDIDO (views/pedido.php)
   Botón "Imprimir factura": abre el diálogo de impresión del
   navegador. La hoja paginaPedido.css oculta todo lo que no es la
   factura (header, footer, botones) al imprimir, así que el papel
   sale solo con el documento.
   ========================================================== */

const botonImprimirFactura = document.getElementById("botonImprimirFactura");

if (botonImprimirFactura) {

    botonImprimirFactura.addEventListener("click", () => {
        window.print();
    });
}
