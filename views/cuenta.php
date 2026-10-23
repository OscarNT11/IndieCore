<?php
// CSS propio de esta vista. DEBE declararse antes de incluir el header,
// que es quien imprime los <link> a las hojas de estilo.
$cssExtra = ['public/css/paginaCuenta.css'];

require __DIR__ . '/header.php';

// $usuario lo rellena UsuarioController::cuenta() con los datos frescos
// de la BD (nombre, email, foto).
$fotoActual = !empty($usuario['foto'])
    ? $usuario['foto']
    : null;

// Historial de pedidos del usuario (PedidoModel::listarHistorialPorUsuario).
// Cada pedido trae: fecha, estado, precio_total, metodo_pago y 'detalles'.
$historialPedidos = $historialPedidos ?? array();

$nombresDeEstado = array(
    'pendiente' => 'Pago pendiente',
    'pagado'    => 'Pago aprobado',
    'rechazado' => 'Pago rechazado',
    'enviado'   => 'Enviado',
    'entregado' => 'Entregado',
    'cancelado' => 'Cancelado',
);

$nombresDeMetodo = array(
    'tarjeta_credito' => 'Tarjeta de crédito',
    'tarjeta_debito'  => 'Tarjeta de débito',
    'transferencia'   => 'Transferencia bancaria',
    'efectivo'        => 'Efectivo al recibir',
);
?>

<section class="cuenta">

    <h1 class="cuenta__titulo">Mi cuenta</h1>

    <!-- ==========================
         AVISOS: éxito de guardado o lista de errores
         ========================== -->

    <?php if (!empty($mensajeOk)): ?>
        <div class="cuenta__aviso cuenta__aviso--ok" role="status">
            <?php echo escapar($mensajeOk); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="cuenta__aviso cuenta__aviso--error" role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo escapar($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- ==========================
         PANEL: foto a la izquierda, datos a la derecha
         enctype="multipart/form-data" es OBLIGATORIO para subir la foto.
         ========================== -->

    <form
        class="cuenta__panel"
        action="index.php?pagina=cuenta"
        method="POST"
        enctype="multipart/form-data">

        <!-- ---------- Columna izquierda: foto de perfil ---------- -->
        <div class="cuenta__foto-columna">

            <div class="cuenta__foto-marco">
                <?php if ($fotoActual): ?>
                    <img
                        class="cuenta__foto"
                        src="<?php echo escapar($fotoActual); ?>?v=<?php echo filemtime(__DIR__ . '/../' . $fotoActual); ?>"
                        alt="Foto de perfil de <?php echo escapar($usuario['nombre']); ?>">
                <?php else: ?>
                    <svg class="cuenta__foto-icono" aria-hidden="true">
                        <use href="public/img/iconos.svg#icono-persona"></use>
                    </svg>
                <?php endif; ?>
            </div>

            <!-- El input real va oculto; el label hace de botón -->
            <input
                type="file"
                name="foto"
                id="foto-perfil"
                class="cuenta__archivo"
                accept="image/png, image/jpeg, image/gif, image/webp">

            <label for="foto-perfil" class="cuenta__subir">
                Subir foto
            </label>

            <p class="cuenta__subir-ayuda">
                JPG, PNG, GIF o WEBP. Máximo 2 MB.
            </p>
        </div>

        <!-- ---------- Columna derecha: datos ---------- -->
        <div class="cuenta__form">

            <div class="cuenta__campo">
                <label for="cuenta-nombre">Nombre de usuario</label>
                <input
                    type="text"
                    name="nombre"
                    id="cuenta-nombre"
                    value="<?php echo escapar($usuario['nombre']); ?>"
                    maxlength="50"
                    required>
            </div>

            <div class="cuenta__campo">
                <label for="cuenta-password">Nueva contraseña</label>
                <input
                    type="password"
                    name="password"
                    id="cuenta-password"
                    placeholder="Déjala vacía para no cambiarla..."
                    autocomplete="new-password">
            </div>

            <div class="cuenta__campo">
                <label for="cuenta-password2">Repetir nueva contraseña</label>
                <input
                    type="password"
                    name="password2"
                    id="cuenta-password2"
                    placeholder="Repite la nueva contraseña..."
                    autocomplete="new-password">
            </div>

            <!-- ---------- Confirmación: sin esto no se cambian
                 el nombre ni la contraseña. ---------- -->
            <div class="cuenta__campo cuenta__campo--confirmar">
                <label for="cuenta-email-confirmacion">
                    Escribe tu correo para confirmar los cambios
                </label>
                <input
                    type="email"
                    name="email_confirmacion"
                    id="cuenta-email-confirmacion"
                    placeholder="Correo de tu cuenta..."
                    autocomplete="off">
                <span class="cuenta__ayuda">
                    Debe coincidir con el correo de tu cuenta.
                </span>
            </div>

            <div class="cuenta__acciones">
                <button type="submit" class="boton boton--principal">
                    Guardar cambios
                </button>

                <a href="index.php?pagina=home" class="boton boton--secundario">
                    Volver al inicio
                </a>

                <a href="index.php?pagina=logout" class="boton boton--salir">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </form>

    <!-- ==========================
         HISTORIAL DE PEDIDOS
         Pedidos del usuario logueado, del más nuevo al más viejo,
         con su estado, método de pago, productos y total.
         ========================== -->
    <section class="cuenta__historial" aria-labelledby="titulo-historial">

        <h2 class="cuenta__historial-titulo" id="titulo-historial">Historial de pedidos</h2>

        <?php if (empty($historialPedidos)): ?>

            <p class="cuenta__historial-vacio">
                Todavía no realizaste ningún pedido.
                <a href="index.php?pagina=catalogo">Ir al catálogo</a>
            </p>

        <?php else: ?>

            <ul class="cuenta__pedidos">

                <?php foreach ($historialPedidos as $pedidoHistorial): ?>

                    <?php
                    $estadoPedido = $pedidoHistorial['estado'] ?? 'pendiente';
                    $detallesDelHistorial = $pedidoHistorial['detalles'] ?? array();
                    $fechaPedido = !empty($pedidoHistorial['fecha'])
                        ? date('d/m/Y H:i', strtotime($pedidoHistorial['fecha']))
                        : '';
                    ?>

                    <li class="cuenta__pedido">

                        <div class="cuenta__pedido-cabecera">
                            <span class="cuenta__pedido-numero">
                                Pedido #<?php echo (int) $pedidoHistorial['id_pedido']; ?>
                            </span>

                            <span class="cuenta__pedido-fecha">
                                <?php echo escapar($fechaPedido); ?>
                            </span>

                            <span class="cuenta__pedido-estado cuenta__pedido-estado--<?php echo escapar($estadoPedido); ?>">
                                <?php echo escapar($nombresDeEstado[$estadoPedido] ?? $estadoPedido); ?>
                            </span>
                        </div>

                        <?php if (!empty($detallesDelHistorial)): ?>
                            <ul class="cuenta__pedido-detalle">
                                <?php foreach ($detallesDelHistorial as $detalleHistorial): ?>
                                    <li>
                                        <span class="cuenta__pedido-producto">
                                            <?php echo escapar($detalleHistorial['nombre_producto'] ?? ('Producto #' . (int) $detalleHistorial['id_producto'])); ?>
                                            <small>×<?php echo (int) $detalleHistorial['cantidad']; ?></small>
                                        </span>
                                        <span class="cuenta__pedido-subtotal">
                                            $<?php echo number_format((float) $detalleHistorial['precio_unitario'] * (int) $detalleHistorial['cantidad'], 2, ',', '.'); ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <div class="cuenta__pedido-pie">
                            <span class="cuenta__pedido-metodo">
                                <?php
                                $metodoDelPedido = $pedidoHistorial['metodo_pago'] ?? '';

                                echo escapar($nombresDeMetodo[$metodoDelPedido] ?? 'Sin método de pago');

                                if (!empty($pedidoHistorial['ultimos4'])) {
                                    echo ' (terminada en ' . escapar($pedidoHistorial['ultimos4']) . ')';
                                }
                                ?>
                            </span>

                            <span class="cuenta__pedido-total">
                                Total: $<?php echo number_format((float) $pedidoHistorial['precio_total'], 2, ',', '.'); ?>
                            </span>
                        </div>

                        <a
                            class="cuenta__pedido-enlace"
                            href="index.php?pagina=pedido&id=<?php echo (int) $pedidoHistorial['id_pedido']; ?>&origen=cuenta">
                            Ver detalle
                        </a>
                    </li>

                <?php endforeach; ?>

            </ul>

        <?php endif; ?>
    </section>
</section>

<!-- ==========================
    Pagina modular de inicio de sesión y registro
    ========================== -->

<?php require __DIR__ . '/modalLogin.php'; ?>

<?php
// No hace falta JS propio: el formulario se envía al servidor.
// (footer.php ya carga storage.js y login.js)
require __DIR__ . '/footer.php';
?>