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
        // Capturar categoria y busqueda
        $idCategoriaSeleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;
        $terminoBusqueda = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

        // Capturar marca y precios del catalogo
        $marcasSeleccionadas = isset($_GET['marca']) && is_array($_GET['marca']) ? $_GET['marca'] : [];
        $precioMin = isset($_GET['precio_min']) && $_GET['precio_min'] !== '' ? $_GET['precio_min'] : null;
        $precioMax = isset($_GET['precio_max']) && $_GET['precio_max'] !== '' ? $_GET['precio_max'] : null;

        // Agrupando en un array 
        $filtros = [
            'categoria' => $idCategoriaSeleccionada,
            'busqueda' => $terminoBusqueda,
            'marca' => $marcasSeleccionadas,
            'precio_min' => $precioMin,
            'precio_max' => $precioMax
        ];

        // Funciones de ProductoModel.php
        $listaDeProductos = $this->productoModel->obtenerFiltrados($filtros);

        $listaDeCategorias = $this->productoModel->listarCategorias();

        require __DIR__ . '/../views/paginaCatalogo.php';
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

        // Productos de la misma categoría, para la sección de relacionados.
        // Si la categoría no existe (producto sin categoría), se devuelve vacío.
        $listaDeProductos = array();

        if (!empty($producto['id_categoria'])) {

            $listaDeProductos = $this->productoModel->listarProductosRelacionados(
                $producto['id_categoria'],
                $producto['id_producto']
            );
        }

        require __DIR__ . '/../views/paginaProducto.php';
    }
}
?>