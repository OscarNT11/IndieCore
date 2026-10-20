/* ==========================================================
   PÁGINA DE CHECKOUT (views/checkout.php)
   Este script SOLO se carga en el checkout.
   1) Muestra u oculta los campos de tarjeta según el método de pago.
   2) Valida el formulario en el navegador antes de enviarlo al servidor
      (la validación real, igual, se repite en PedidoController).
   ========================================================== */

const formularioCheckout = document.getElementById("formularioCheckout");

const bloqueTarjeta = document.getElementById("checkoutDatosTarjeta");

const camposTarjeta = bloqueTarjeta
    ? bloqueTarjeta.querySelectorAll("input")
    : [];

const opcionesMetodo = document.querySelectorAll('input[name="metodo_pago"]');

/* Métodos que se pagan con tarjeta y, por lo tanto, piden los datos de la misma. */
const METODOS_CON_TARJETA = ["tarjeta_credito", "tarjeta_debito"];

function metodoElegido() {

    const seleccionada = document.querySelector('input[name="metodo_pago"]:checked');

    return seleccionada ? seleccionada.value : "";
}

/* Los campos de tarjeta se deshabilitan cuando no corresponde: así el
   navegador no los exige y no viajan datos de tarjeta innecesarios. */
function actualizarCamposTarjeta() {

    if (!bloqueTarjeta) return;

    const conTarjeta = METODOS_CON_TARJETA.includes(metodoElegido());

    bloqueTarjeta.hidden = !conTarjeta;

    camposTarjeta.forEach((campo) => {
        campo.disabled = !conTarjeta;
    });
}

opcionesMetodo.forEach((opcion) => {
    opcion.addEventListener("change", actualizarCamposTarjeta);
});

actualizarCamposTarjeta();

/* Los avisos de "falta llenar" se limpian apenas el usuario
   escribe o elige algo en el campo. */
if (formularioCheckout) {

    formularioCheckout.addEventListener("input", (evento) => {

        if (evento.target.classList.contains("campo-con-error")) {
            limpiarError(evento.target);
        }
    });

    formularioCheckout.addEventListener("change", (evento) => {

        if (evento.target.classList.contains("campo-con-error")) {
            limpiarError(evento.target);
        }
    });
}

/* ==========================================================
   VALIDACIÓN ANTES DE ENVIAR
   Si algo falta, se frena el envío y se avisa en el mismo campo,
   igual que hacen login.js y carrito.js.
   ========================================================== */

function marcarError(campo, mensaje) {

    campo.classList.add("campo-con-error");

    campo.setAttribute("aria-invalid", "true");

    const aviso = campo.parentElement.querySelector(".checkout__error");

    if (aviso) aviso.textContent = mensaje;
}

function limpiarError(campo) {

    campo.classList.remove("campo-con-error");

    campo.removeAttribute("aria-invalid");

    const aviso = campo.parentElement.querySelector(".checkout__error");

    if (aviso) aviso.textContent = "";
}

function limpiarErrores() {

    formularioCheckout
        .querySelectorAll(".campo-con-error")
        .forEach(limpiarError);
}

/* Números de tarjeta: se guarda la cantidad de dígitos y si son todos números. */
function soloDigitos(valor) {
    return /^[0-9]+$/.test(valor.replace(/\s+/g, ""));
}

if (formularioCheckout) {

    formularioCheckout.addEventListener("submit", (evento) => {

        limpiarErrores();

        let hayError = false;

        const campoNombre = document.getElementById("envio-nombre");
        const campoDireccion = document.getElementById("envio-direccion");
        const campoCiudad = document.getElementById("envio-ciudad");
        const campoDepartamento = document.getElementById("envio-departamento");
        const campoTelefono = document.getElementById("envio-telefono");

        const obligatorios = [
            [campoNombre, "Ingresá el nombre de quien recibe"],
            [campoDireccion, "Ingresá la dirección de envío"],
            [campoCiudad, "Ingresá la ciudad o localidad"],
            [campoDepartamento, "Elegí un departamento"],
            [campoTelefono, "Ingresá un teléfono de contacto"],
        ];

        obligatorios.forEach(([campo, mensaje]) => {

            if (campo && campo.value.trim() === "") {
                marcarError(campo, mensaje);
                hayError = true;
            }
        });

        if (campoTelefono && campoTelefono.value.trim() !== "") {

            const digitosTelefono = campoTelefono.value.replace(/[^0-9]/g, "");

            if (digitosTelefono.length < 6) {
                marcarError(campoTelefono, "El teléfono no parece válido");
                hayError = true;
            }
        }

        // --- Datos de tarjeta (solo si el método los pide) ---
        if (METODOS_CON_TARJETA.includes(metodoElegido())) {

            const campoNumero = document.getElementById("tarjeta-numero");
            const campoTitular = document.getElementById("tarjeta-titular");
            const campoVencimiento = document.getElementById("tarjeta-vencimiento");
            const campoCvv = document.getElementById("tarjeta-cvv");

            if (campoNumero) {

                const digitos = campoNumero.value.replace(/\D/g, "");

                if (digitos === "") {
                    marcarError(campoNumero, "Ingresá el número de tarjeta");
                    hayError = true;

                } else if (!soloDigitos(campoNumero.value) || digitos.length < 13 || digitos.length > 19) {
                    marcarError(campoNumero, "El número de tarjeta no es válido");
                    hayError = true;
                }
            }

            if (campoTitular && campoTitular.value.trim() === "") {
                marcarError(campoTitular, "Ingresá el nombre del titular");
                hayError = true;
            }

            if (campoVencimiento) {

                const vencimientoValido = /^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(campoVencimiento.value.trim());

                if (!vencimientoValido) {
                    marcarError(campoVencimiento, "Usá el formato MM/AA");
                    hayError = true;
                }
            }

            if (campoCvv) {

                const cvv = campoCvv.value.trim();

                if (cvv.length < 3 || !soloDigitos(cvv)) {
                    marcarError(campoCvv, "El CVV debe tener 3 o 4 dígitos");
                    hayError = true;
                }
            }
        }

        if (hayError) {
            evento.preventDefault();

            // Se lleva al usuario al primer campo que falta llenar.
            const primerError = formularioCheckout.querySelector(".campo-con-error");

            if (primerError) primerError.focus();
        }
    });
}

/* ==========================================================
   NÚMERO DE TARJETA: se agrupa de a 4 dígitos mientras se escribe.
   ========================================================== */

const campoNumeroTarjeta = document.getElementById("tarjeta-numero");

if (campoNumeroTarjeta) {

    campoNumeroTarjeta.addEventListener("input", () => {

        const digitos = campoNumeroTarjeta.value.replace(/\D/g, "").slice(0, 19);

        campoNumeroTarjeta.value = digitos.replace(/(\d{4})(?=\d)/g, "$1 ");
    });
}

/* ==========================================================
   VENCIMIENTO: se agrega la barra automáticamente (MM/AA).
   ========================================================== */

const campoVencimientoTarjeta = document.getElementById("tarjeta-vencimiento");

if (campoVencimientoTarjeta) {

    campoVencimientoTarjeta.addEventListener("input", () => {

        let digitos = campoVencimientoTarjeta.value.replace(/\D/g, "").slice(0, 4);

        if (digitos.length > 2) {
            digitos = digitos.slice(0, 2) + "/" + digitos.slice(2);
        }

        campoVencimientoTarjeta.value = digitos;
    });
}
