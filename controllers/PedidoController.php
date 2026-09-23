<?php

require_once __DIR__ . '/../models/PedidoModel.php';

class PedidoController {

    private $pedidoModel;

    public function __construct($conexion) {
        $this->pedidoModel = new PedidoModel($conexion);
    }

    /* Guarda el pedido del carrito y, si sale bien, muestra la página
       de confirmación con el resumen. */
    public function confirmarPedido() {
        if (!isset($_POST['carritoJson']) || empty($_POST['carritoJson'])) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        $carritoRecibido = json_decode($_POST['carritoJson'], true);

        if (!is_array($carritoRecibido) || empty($carritoRecibido)) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        // El total se recalcula en el servidor, nunca se confía al cliente.
        $lineasDelPedido = array();
        $totalDelPedido = 0;

        foreach ($carritoRecibido as $itemDelCarrito) {
            if (!isset($itemDelCarrito['id'], $itemDelCarrito['cantidad'], $itemDelCarrito['precio'])) {
                continue;
            }

            $idProducto = (int) $itemDelCarrito['id'];
            $cantidad = (int) $itemDelCarrito['cantidad'];
            $precioUnitario = (float) $itemDelCarrito['precio'];

            if ($idProducto <= 0 || $cantidad <= 0) {
                continue;
            }

            $lineasDelPedido[] = array(
                'id' => $idProducto,
                'nombre' => isset($itemDelCarrito['nombre'])
                    ? (string) $itemDelCarrito['nombre']
                    : ('Producto #' . $idProducto),
                'cantidad' => $cantidad,
                'precio' => $precioUnitario,
            );

            $totalDelPedido += $precioUnitario * $cantidad;
        }

        if (empty($lineasDelPedido)) {
            header('Location: index.php?pagina=carrito');
            exit;
        }

        // El pedido y su detalle se guardan juntos: si algo falla, se
        // revierte todo para no dejar un pedido incompleto en la BD.
        $this->pedidoModel->iniciarTransaccion();

        try {
            $idPedidoCreado = $this->pedidoModel->crearPedido($totalDelPedido);

            if ($idPedidoCreado === false) {
                throw new Exception('No se pudo crear el pedido.');
            }

            foreach ($lineasDelPedido as $linea) {
                $detalleGuardado = $this->pedidoModel->agregarDetalleAlPedido(
                    $idPedidoCreado,
                    $linea['id'],
                    $linea['cantidad'],
                    $linea['precio']
                );

                if ($detalleGuardado === false) {
                    throw new Exception('No se pudo guardar una línea del pedido.');
                }
            }

            $this->pedidoModel->confirmarTransaccion();
        } catch (Exception $error) {
            $this->pedidoModel->revertirTransaccion();

            header('Location: index.php?pagina=carrito&pedido=error');
            exit;
        }

        $this->mostrarConfirmacion($idPedidoCreado, $lineasDelPedido, $totalDelPedido);
    }

    /* Renderiza views/pedido.php, que usa estas variables como locales. */
    private function mostrarConfirmacion($idPedidoCreado = null, $carritoRecibido = array(), $totalDelPedido = 0) {
        // Acceso directo a ?pagina=pedido sin pasar por el carrito.
        if ($idPedidoCreado === null || empty($carritoRecibido)) {
            header('Location: index.php?pagina=catalogo');
            exit;
        }

        require __DIR__ . '/../views/pedido.php';
    }
}