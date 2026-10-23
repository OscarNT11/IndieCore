<?php
// controllers/AdminPedidoController.php
require_once __DIR__ . '/../config/auth.php';

/* Panel de pedidos: muestra todos los pedidos realizados por cualquier
   usuario (una "factura" por pedido). Solo lectura: no se modifica nada.
   Disponible para administradores y operadores, igual que el panel de
   productos. */
class AdminPedidoController {

    private $conexion;
    private $pedidoModel;

    public function __construct($conexion) {

        if (!esGestor()) {
            http_response_code(403);
            require __DIR__ . '/../views/sin_permiso.php';
            exit;
        }

        $this->conexion = $conexion;
        $this->pedidoModel = new PedidoModel($conexion);
    }

    /* index.php?pagina=admin_pedidos */
    public function listar() {

        $pedidos = $this->pedidoModel->listarTodosAdmin();

        $resumen = $this->pedidoModel->resumenAdmin();

        $cssExtra = ['public/css/paginaPanel.css'];
        $claseBody = 'body-panel';

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/admin/pedido_lista.php';
        require __DIR__ . '/../views/footer_simple.php';
    }
}
