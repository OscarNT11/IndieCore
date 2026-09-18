<?php

class ProductoController {
    
    private $conexion;
    private $productoModel;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->productoModel = new ProductoModel($conexion);
    }

    public function mostrarInicio() {
        // La página de inicio muestra los productos recientes ("Lo nuevo")
        // y los destacados, ambos provienen del mismo modelo.
        $listaDeProductos = $this->productoModel->listarProductos();

        require __DIR__ . '/../views/paginaInicio.php';
    }

    public function mostrarCatalogo() {
        $idCategoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;

        $listaDeProductos  = $this->productoModel->listarProductos($idCategoriaSeleccionada);
        $listaDeCategorias = $this->productoModel->listarCategorias();

        require __DIR__ . '/../views/paginaListado.php';
    }

    public function mostrarDetalle() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            require __DIR__ . '/../views/header.php';
            echo "<p>Producto no encontrado.</p>";
            require __DIR__ . '/../views/footer.php';
            exit;
        }

        $idProducto = (int) $_GET['id'];
        $producto = $this->productoModel->obtenerProductoPorId($idProducto);

        if (!$producto) {
            require __DIR__ . '/../views/header.php';
            echo "<p>El producto solicitado no existe.</p>";
            require __DIR__ . '/../views/footer.php';
            exit;
        }

        require __DIR__ . '/../views/paginaProducto.php';
    }
}
?>