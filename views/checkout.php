<?php
// ==========================
// Página de confirmación del pedido: datos de envío + método de pago.
// La carga PedidoController::mostrarCheckout(), que arma estas variables:
//   $lineasDelPedido  -> productos del carrito con precio recalculado
//   $totalDelPedido   -> total en el servidor
//   $departamentos    -> lista de departamentos de Uruguay
//   $datosEnvio       -> lo que el usuario escribió (al volver con errores)
//   $erroresCheckout  -> errores de validación del servidor
// ==========================

// La hoja de esta vista debe fijarse antes de header.php, que lee $cssExtra.
$cssExtra = ['public/css/paginaCheckout.css'];
require __DIR__ . '/header.php';

$envio = isset($datosEnvio) ? $datosEnvio : array();
$metodoSeleccionado = isset($envio['metodo_pago']) ? $envio['metodo_pago'] : 'tarjeta_credito';
?>

<section class="checkout">

    <h1 class="checkout__titulo">Confirmar pedido</h1>

    <?php if (!empty($erroresCheckout)): ?>
        <ul class="checkout__errores" role="alert">
            <?php foreach ($erroresCheckout as $error): ?>
                <li><?php echo escapar($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="checkout__layout">

        <!-- ==========================
             Formulario: envío y pago
             ========================== -->
        <form id="formularioCheckout" class="checkout__form" action="index.php?pagina=confirmar-pedido" method="POST" novalidate>

            <fieldset class="checkout__bloque">
                <legend>Datos de envío</legend>

                <div class="checkout__campo">
                    <label for="envio-nombre">Nombre y apellido</label>
                    <input type="text" id="envio-nombre" name="envio_nombre"
                           placeholder="Quién recibe el pedido"
                           value="<?php echo escapar($envio['nombre'] ?? ''); ?>" required>
                    <span class="checkout__error" aria-live="polite"></span>
                </div>

                <div class="checkout__campo">
                    <label for="envio-direccion">Dirección</label>
                    <input type="text" id="envio-direccion" name="envio_direccion"
                           placeholder="Calle, número, apartamento"
                           value="<?php echo escapar($envio['direccion'] ?? ''); ?>" required>
                    <span class="checkout__error" aria-live="polite"></span>
                </div>

                <div class="checkout__fila">
                    <div class="checkout__campo">
                        <label for="envio-ciudad">Ciudad / localidad</label>
                        <input type="text" id="envio-ciudad" name="envio_ciudad"
                               placeholder="Ciudad o localidad"
                               value="<?php echo escapar($envio['ciudad'] ?? ''); ?>" required>
                        <span class="checkout__error" aria-live="polite"></span>
                    </div>

                    <div class="checkout__campo">
                        <label for="envio-departamento">Departamento</label>
                        <select id="envio-departamento" name="envio_departamento" required>
                            <option value="">Elegí un departamento</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?php echo escapar($departamento); ?>"
                                    <?php echo (($envio['departamento'] ?? '') === $departamento) ? 'selected' : ''; ?>>
                                    <?php echo escapar($departamento); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="checkout__error" aria-live="polite"></span>
                    </div>
                </div>

                <div class="checkout__campo">
                    <label for="envio-telefono">Teléfono de contacto</label>
                    <input type="tel" id="envio-telefono" name="envio_telefono"
                           placeholder="Ej: 099 123 456"
                           value="<?php echo escapar($envio['telefono'] ?? ''); ?>" required>
                    <span class="checkout__error" aria-live="polite"></span>
                </div>
            </fieldset>

            <fieldset class="checkout__bloque">
                <legend>Método de pago</legend>

                <div class="checkout__metodos">

                    <label class="checkout__metodo">
                        <input type="radio" name="metodo_pago" value="tarjeta_credito"
                            <?php echo ($metodoSeleccionado === 'tarjeta_credito') ? 'checked' : ''; ?>>
                        <span>Tarjeta de crédito</span>
                    </label>

                    <label class="checkout__metodo">
                        <input type="radio" name="metodo_pago" value="tarjeta_debito"
                            <?php echo ($metodoSeleccionado === 'tarjeta_debito') ? 'checked' : ''; ?>>
                        <span>Tarjeta de débito</span>
                    </label>

                    <label class="checkout__metodo">
                        <input type="radio" name="metodo_pago" value="transferencia"
                            <?php echo ($metodoSeleccionado === 'transferencia') ? 'checked' : ''; ?>>
                        <span>Transferencia bancaria</span>
                    </label>

                    <label class="checkout__metodo">
                        <input type="radio" name="metodo_pago" value="efectivo"
                            <?php echo ($metodoSeleccionado === 'efectivo') ? 'checked' : ''; ?>>
                        <span>Efectivo al recibir</span>
                    </label>
                </div>

                <!-- Estos campos solo se muestran (y se exigen) con tarjeta;
                    checkout.js los habilita según el método elegido. -->
                <div class="checkout__tarjeta" id="checkoutDatosTarjeta" hidden>
                    <div class="checkout__campo">
                        <label for="tarjeta-numero">Número de tarjeta</label>
                        <input type="text" id="tarjeta-numero" name="tarjeta_numero"
                               autocomplete="off" data-lpignore="true" data-1p-ignore="true"
                               inputmode="numeric" maxlength="19"
                               placeholder="Número de tarjeta"
                               value="<?php echo escapar($envio['tarjeta_numero'] ?? ''); ?>">
                        <span class="checkout__error" aria-live="polite"></span>
                    </div>

                    <div class="checkout__campo">
                        <label for="tarjeta-titular">Titular de la tarjeta</label>
                        <input type="text" id="tarjeta-titular" name="tarjeta_titular"
                               autocomplete="off" data-lpignore="true" data-1p-ignore="true"
                               placeholder="Nombre como figura en la tarjeta"
                               value="<?php echo escapar($envio['tarjeta_titular'] ?? ''); ?>">
                        <span class="checkout__error" aria-live="polite"></span>
                    </div>

                    <div class="checkout__fila">
                        <div class="checkout__campo">
                            <label for="tarjeta-vencimiento">Vencimiento (MM/AA)</label>
                            <input type="text" id="tarjeta-vencimiento" name="tarjeta_vencimiento"
                                   autocomplete="off" data-lpignore="true" data-1p-ignore="true"
                                   inputmode="numeric" maxlength="5" placeholder="MM/AA"
                                   value="<?php echo escapar($envio['tarjeta_vencimiento'] ?? ''); ?>">
                            <span class="checkout__error" aria-live="polite"></span>
                        </div>

                        <div class="checkout__campo">
                            <label for="tarjeta-cvv">CVV</label>
                            <input type="text" id="tarjeta-cvv" name="tarjeta_cvv"
                                   autocomplete="off" data-lpignore="true" data-1p-ignore="true"
                                   inputmode="numeric" maxlength="4" placeholder="CVV">
                            <span class="checkout__error" aria-live="polite"></span>
                        </div>
                    </div>

                </div>
            </fieldset>

            <div class="checkout__acciones">
                <a class="boton boton--secundario" href="index.php?pagina=carrito">Volver al carrito</a>
                <button type="submit" class="boton boton--principal">Confirmar pedido</button>
            </div>
        </form>

        <!-- ==========================
             Resumen del pedido
             ========================== -->
        <aside class="checkout__resumen">
            <h2>Resumen</h2>

            <ul class="checkout__lista">
                <?php foreach ($lineasDelPedido as $linea): ?>
                    <li class="checkout__item">
                        <span class="checkout__item-nombre">
                            <?php echo escapar($linea['nombre']); ?>
                            <small>×<?php echo (int) $linea['cantidad']; ?></small>
                        </span>
                        <span class="checkout__item-precio">
                            $<?php echo number_format($linea['precio'] * $linea['cantidad'], 2, ',', '.'); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="checkout__total">
                Total: <strong>$<?php echo number_format($totalDelPedido, 2, ',', '.'); ?></strong>
            </p>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// checkout.js habilita los campos de tarjeta según el método elegido.
// (footer.php ya carga storage.js y login.js)
$scriptsExtra = ['public/js/checkout.js'];
require __DIR__ . '/footer.php';
?>
