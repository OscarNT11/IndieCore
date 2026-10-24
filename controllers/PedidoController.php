<?php

require_once __DIR__ . '/../models/PedidoModel.php';
require_once __DIR__ . '/../models/PagoModel.php';
require_once __DIR__ . '/../models/ProductoModel.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/CarritoModel.php';

class PedidoController {

    private $conexion;
    private $pedidoModel;
    private $pagoModel;
    private $productoModel;
    private $carritoModel;

    /* Departamentos de Uruguay: alimentan el <select> del checkout. */
    private $departamentos = [
        'Artigas', 'Canelones', 'Cerro Largo', 'Colonia', 'Durazno', 'Flores',
        'Florida', 'Lavalleja', 'Maldonado', 'Montevideo', 'Paysandú',
        'Río Negro', 'Rivera', 'Rocha', 'Salto', 'San José', 'Soriano',
        'Tacuarembó', 'Treinta y Tres'
    ];

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->pedidoModel = new PedidoModel($conexion);
        $this->pagoModel = new PagoModel($conexion);
        $this->productoModel = new ProductoModel($conexion);
        $this->carritoModel = new CarritoModel($conexion);
    }

    /* ==========================================================
       PASO 1: CHECKOUT (index.php?pagina=checkout)
       Recibe el carrito del navegador, recalcula todo contra la BD y
       muestra el formulario de envío + método de pago.
       ========================================================== */
    
    // Se modificó el mostrarCheckout con la nueva versión de carrito

    public function mostrarCheckout() {

        if (!usuarioLogueado()) {
            header('Location: index.php?pagina=carrito&pedido=sesion');
            exit;
        }

        $idUsuario = (int) $_SESSION['id_usuario'];

        // Obtener el carrito del usuario
        $carritoRecibido = $this->carritoModel->obtenerDetallesCarrito($idUsuario);

        if ($carritoRecibido === false) {
            $this->registrarError('mostrarCheckout: no se pudo consultar el carrito SQL.');
            header('Location: index.php?pagina=carrito&pedido=error');
            exit;
        }

        if (empty($carritoRecibido)) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        $lineasDelPedido = $this->recalcularLineas($carritoRecibido);

        if (empty($lineasDelPedido)) {
            header('Location: index.php?pagina=carrito&pedido=vacio');
            exit;
        }

        $totalDelPedido = $this->sumarLineas($lineasDelPedido);

        $departamentos = $this->departamentos;

        $usuario = usuarioActual();

        $datosEnvio = array(
            'nombre' => $usuario['nombre'] ?? '',
            'direccion' => '',
            'ciudad' => '',
            'departamento' => '',
            'telefono' => '',
            'metodo_pago' => 'tarjeta_credito',
        );

        $erroresCheckout = array();

        require __DIR__ . '/../views/checkout.php';
    }


    /* ==========================================================
       PASO 2: CONFIRMAR (index.php?pagina=confirmar-pedido, POST)
       Valida el envío y el pago, guarda pedido + detalle + pago y
       muestra la confirmación. Si algo falla, vuelve al checkout con
       los errores y SIN vaciar el carrito.
       ========================================================== */
    public function confirmarPedido() {

        if (!usuarioLogueado()) {
            header('Location: index.php?pagina=carrito&pedido=sesion');
            exit;
        }

        $idUsuario = (int) $_SESSION['id_usuario'];

        // Volver a consultar SQL al confirmar: no confiar en el
        // carrito que se mostró anteriormente en el navegador
        // Esto por si el usuario logró modificar cosas en el frontend 
        $carritoRecibido = $this->carritoModel->obtenerDetallesCarrito($idUsuario);

        if ($carritoRecibido === false) {
        $this->registrarError('confirmarPedido: no se pudo consultar el carrito SQL.');
        header('Location: index.php?pagina=carrito&pedido=error');
        exit;
        }

        if (empty($carritoRecibido)) {
        header('Location: index.php?pagina=carrito');
        exit;
        }

        $lineasDelPedido = $this->recalcularLineas($carritoRecibido);

        if (empty($lineasDelPedido)) {
            header('Location: index.php?pagina=carrito&pedido=vacio');
            exit;
        }

        $totalDelPedido = $this->sumarLineas($lineasDelPedido);

        // ---------- Validación de los datos de envío y del pago ----------
        $datosEnvio = $this->leerDatosEnvio();
        $erroresCheckout = $this->validarCheckout($datosEnvio);

        if (!empty($erroresCheckout)) {
            // Se vuelve a mostrar el checkout con lo que el usuario escribió
            // y la lista de errores. El carrito sigue intacto en el navegador.
            $departamentos = $this->departamentos;

            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $idUsuario = (int) $_SESSION['id_usuario'];

        // Datos de tarjeta: solo interesan los últimos 4 dígitos.
        $ultimos4 = null;

        // SIMULACIÓN DEL PAGO (no hay pasarela real ni algoritmos válidos):
        //   último dígito PAR   -> pago aprobado
        //   último dígito IMPAR -> pago rechazado
        // Transferencia y efectivo quedan 'pendiente' hasta cobrarse.
        $resultadoPago = 'aprobado';

        if ($this->esMetodoConTarjeta($datosEnvio['metodo_pago'])) {

            $soloDigitos = preg_replace('/\D/', '', $datosEnvio['tarjeta_numero']);

            $ultimos4 = substr($soloDigitos, -4);

            $ultimoDigito = (int) substr($soloDigitos, -1);

            $resultadoPago = ($ultimoDigito % 2 === 0) ? 'aprobado' : 'rechazado';
        }

        // ---------- Tratamiento del pago rechazado ----------
        // Si la tarjeta es rechazada, no se registra la compra,
        // no se descuenta el stock y se conserva el carrito SQL.

        if ($resultadoPago === 'rechazado') {

            $erroresCheckout = array(
                'El pago fue rechazado. Verificá los datos de la tarjeta o utilizá otro método de pago.'
            );

            $departamentos = $this->departamentos;

            require __DIR__ . '/../views/checkout.php';
            return;
        }

        // ---------- Guardado ----------
        // El pedido y su detalle se guardan juntos: si algo falla, se
        // revierte todo para no dejar un pedido incompleto en la BD.
        
        try {
            $this->pedidoModel->iniciarTransaccion();
            $idPedidoCreado = $this->pedidoModel->crearPedido(
                $totalDelPedido,
                $datosEnvio,
                $idUsuario
            );

            if ($idPedidoCreado === false) {
                throw new Exception('No se pudo crear el pedido.');
            }

            foreach ($lineasDelPedido as $linea) {
                $detalleGuardado = $this->pedidoModel->agregarDetalleAlPedido(
                    $idPedidoCreado,
                    $linea['id'],
                    $linea['cantidad'],
                    $linea['precio'],
                    $linea['nombre']
                );

                if ($detalleGuardado === false) {
                    throw new Exception('No se pudo guardar una línea del pedido.');
                }

                // El stock se descuenta en la MISMA transacción del pedido:
                // si una línea no se puede descontar (sin stock suficiente),
                // se revierte todo y no queda ni pedido ni stock tocado.
                $stockDescontado = $this->productoModel->descontarStock(
                    $linea['id'],
                    $linea['cantidad']
                );

                if ($stockDescontado === false) {
                    throw new Exception('No hay stock suficiente del producto ' . $linea['id'] . '.');
                }
            }
            // Registrar el pago dentro de la misma transacción del pedido.
            // Si falla, la excepción provoca que se reviertan todas las operaciones.
            $pagoRegistrado = $this->pagoModel->registrarIntentoEnTransaccion(
                $idPedidoCreado,
                $totalDelPedido,
                $datosEnvio['metodo_pago'],
                $ultimos4,
                $resultadoPago
            );

            if ($pagoRegistrado === false) {
                throw new Exception('No se pudo registrar el pago.');
            }

            // Confirmar solamente cuando el pedido, sus detalles, el stock
            // y el pago se hayan procesado correctamente.
            $this->pedidoModel->confirmarTransaccion();

        } catch (Throwable $error) {
            $this->pedidoModel->revertirTransaccion();

            // El mensaje que ve el usuario no dice QUÉ falló. La causa real
            // se anota en storage/logs/pedidos.log para poder revisarla.
            $this->registrarError('confirmarPedido: ' . $error->getMessage());

            $erroresCheckout = array('No se pudo registrar el pedido. Intentá nuevamente.');
            $departamentos = $this->departamentos;

            require __DIR__ . '/../views/checkout.php';
            return;
        }

        $this->carritoModel->vaciarCarrito($idUsuario);
        
        $this->mostrarConfirmacion($idPedidoCreado);
    }

    /* ==========================================================
       PASO 3: CONFIRMACIÓN (index.php?pagina=pedido&id=N)
       Lee el pedido YA guardado desde la BD. Nunca usa los datos
       del POST: si el usuario recarga, ve lo mismo.
       ========================================================== */
    public function mostrarConfirmacion($idPedidoCreado = null) {

        if (!usuarioLogueado()) {
            header('Location: index.php?pagina=home');
            exit;
        }

        // Al llamarlo desde confirmarPedido() el id viene por parámetro;
        // si se entra por URL, se toma de ?id=.
        if ($idPedidoCreado === null) {
            $idPedidoCreado = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        }

        $idUsuario = (int) $_SESSION['id_usuario'];

        // obtenerPorId filtra por id_usuario: nadie ve pedidos ajenos.
        $pedido = $this->pedidoModel->obtenerPorId($idPedidoCreado, $idUsuario);

        if (!$pedido) {
            header('Location: index.php?pagina=catalogo');
            exit;
        }

        $detallesDelPedido = $this->pedidoModel->obtenerDetallesPorPedido($idPedidoCreado);

        $pagoDelPedido = $this->pagoModel->obtenerPorPedido($idPedidoCreado);

        // views/pedido.php espera estas variables.
        $totalDelPedido = $pedido['precio_total'];
        $carritoRecibido = $this->lineasDesdeDetalles($detallesDelPedido);

        require __DIR__ . '/../views/pedido.php';
    }

    /* ==========================================================
       AYUDANTES PRIVADOS
       ========================================================== */

    /* Recalcula las líneas del pedido contra la BD: se toman el nombre y
       el precio REALES del producto, nunca los que manda el navegador.
       Además se respeta el stock disponible. */
    private function recalcularLineas($carritoRecibido) {

        $lineasDelPedido = array();

        foreach ($carritoRecibido as $itemDelCarrito) {

            if (!is_array($itemDelCarrito) || !isset($itemDelCarrito['id_producto'])) {
                continue;
            }

            $idProducto = (int) $itemDelCarrito['id_producto'];
            $cantidad = isset($itemDelCarrito['cantidad']) ? (int) $itemDelCarrito['cantidad'] : 0;

            if ($idProducto <= 0 || $cantidad <= 0) {
                continue;
            }

            $producto = $this->productoModel->obtenerProductoPorId($idProducto);

            if (!$producto) {
                continue;
            }

            // Si el producto ya no tiene stock, la línea no entra al pedido.
            $stockDisponible = (int) $producto['stock'];

            if ($stockDisponible <= 0) {
                continue;
            }

            $cantidad = min($cantidad, $stockDisponible);

            $lineasDelPedido[] = array(
                'id' => $idProducto,
                'nombre' => (string) $producto['nombre'],
                'cantidad' => $cantidad,
                'precio' => (float) $producto['precio'],
            );
        }

        return $lineasDelPedido;
    }

    /* Suma los subtotales de las líneas ya recalculadas. */
    private function sumarLineas($lineasDelPedido) {

        $total = 0;

        foreach ($lineasDelPedido as $linea) {
            $total += $linea['precio'] * $linea['cantidad'];
        }

        return $total;
    }

    /* Convierte las filas de `detalles_pedido` al formato que usa la vista. */
    private function lineasDesdeDetalles($detallesDelPedido) {

        $lineas = array();

        foreach ($detallesDelPedido as $detalle) {

            $lineas[] = array(
                'id' => (int) $detalle['id_producto'],
                'nombre' => $detalle['nombre_producto'] ?? ('Producto #' . (int) $detalle['id_producto']),
                'cantidad' => (int) $detalle['cantidad'],
                'precio' => (float) $detalle['precio_unitario'],
            );
        }

        return $lineas;
    }

    /* Recoge y limpia el texto enviado por el formulario de checkout. */
    private function leerDatosEnvio() {

        return array(
            'nombre' => trim((string) ($_POST['envio_nombre'] ?? '')),
            'direccion' => trim((string) ($_POST['envio_direccion'] ?? '')),
            'ciudad' => trim((string) ($_POST['envio_ciudad'] ?? '')),
            'departamento' => trim((string) ($_POST['envio_departamento'] ?? '')),
            'telefono' => trim((string) ($_POST['envio_telefono'] ?? '')),
            'metodo_pago' => trim((string) ($_POST['metodo_pago'] ?? '')),
            'tarjeta_numero' => trim((string) ($_POST['tarjeta_numero'] ?? '')),
            'tarjeta_titular' => trim((string) ($_POST['tarjeta_titular'] ?? '')),
            'tarjeta_vencimiento' => trim((string) ($_POST['tarjeta_vencimiento'] ?? '')),
            'tarjeta_cvv' => trim((string) ($_POST['tarjeta_cvv'] ?? '')),
        );
    }

    /* ¿El método de pago se hace con tarjeta? */
    private function esMetodoConTarjeta($metodoPago) {

        return $metodoPago === 'tarjeta_credito' || $metodoPago === 'tarjeta_debito';
    }

    /* Anota un error en storage/logs/pedidos.log con la fecha y el mensaje.
       Ayuda a diagnosticar sin exponer detalles internos al usuario final. */
    private function registrarError($mensaje) {

        $carpeta = __DIR__ . '/../storage/logs';

        if (!is_dir($carpeta)) {
            @mkdir($carpeta, 0755, true);
        }

        $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;

        @file_put_contents($carpeta . '/pedidos.log', $linea, FILE_APPEND);
    }

    /* Valida envío y pago. Devuelve la lista de errores (vacía si todo bien).
       La validación se repite aquí aunque el JS ya la haga: el navegador no
       es de fiar y el POST se puede fabricar a mano. */
    private function validarCheckout($datosEnvio) {

        $errores = array();

        // ---------- Datos de envío ----------
        if (mb_strlen($datosEnvio['nombre']) < 3) {
            $errores[] = 'El nombre de quien recibe debe tener al menos 3 caracteres.';
        }

        if (mb_strlen($datosEnvio['direccion']) < 5) {
            $errores[] = 'Ingresá una dirección de envío válida.';
        }

        if ($datosEnvio['ciudad'] === '') {
            $errores[] = 'Ingresá la ciudad o localidad.';
        }

        if (!in_array($datosEnvio['departamento'], $this->departamentos, true)) {
            $errores[] = 'Elegí un departamento de la lista.';
        }

        $digitosTelefono = preg_replace('/\D/', '', $datosEnvio['telefono']);

        if (strlen($digitosTelefono) < 6 || strlen($digitosTelefono) > 15) {
            $errores[] = 'Ingresá un teléfono de contacto válido.';
        }

        // ---------- Método de pago ----------
        if (!in_array($datosEnvio['metodo_pago'], PagoModel::METODOS_VALIDOS, true)) {
            $errores[] = 'Elegí un método de pago.';

            // Sin método válido no se pueden validar los datos de tarjeta.
            return $errores;
        }

        if (!$this->esMetodoConTarjeta($datosEnvio['metodo_pago'])) {
            return $errores;
        }

        // ---------- Datos de la tarjeta ----------
        $digitosTarjeta = preg_replace('/\D/', '', $datosEnvio['tarjeta_numero']);

        if (strlen($digitosTarjeta) < 13 || strlen($digitosTarjeta) > 19) {
            $errores[] = 'El número de tarjeta no es válido.';
        }

        if (mb_strlen($datosEnvio['tarjeta_titular']) < 3) {
            $errores[] = 'Ingresá el nombre del titular de la tarjeta.';
        }

        // Vencimiento en formato MM/AA (o MMAA).
        if (preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $datosEnvio['tarjeta_vencimiento'], $coincidencia)) {

            $mes = (int) $coincidencia[1];
            $anio = 2000 + (int) $coincidencia[2];

            $ultimoDiaDelMes = mktime(23, 59, 59, $mes + 1, 0, $anio);

            if ($ultimoDiaDelMes < time()) {
                $errores[] = 'La tarjeta está vencida.';
            }

        } else {
            $errores[] = 'El vencimiento de la tarjeta debe tener el formato MM/AA.';
        }

        if (!preg_match('/^[0-9]{3,4}$/', $datosEnvio['tarjeta_cvv'])) {
            $errores[] = 'El CVV debe tener 3 o 4 dígitos.';
        }

        return $errores;
    }
}