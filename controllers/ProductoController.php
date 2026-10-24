<?php
require_once __DIR__ . '/../models/ReseniaModel.php';

class ProductoController {

    private $conexion;
    private $productoModel;
    private $reseniaModel;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->productoModel = new ProductoModel($conexion);
        $this->reseniaModel = new ReseniaModel($conexion);
    }

    public function mostrarInicio() {
        // La página de inicio tiene dos secciones con criterios distintos:
        //  - "Lo nuevo": los productos más recientes primero (id más alto).
        //  - "Destacados": mejor promedio de estrellas y, a igualdad,
        //    el producto con más reseñas.
        $productosNuevos = $this->productoModel->listarNuevos();
        $productosDestacados = $this->productoModel->listarDestacados();

        // Alias con el que trabajaban las vistas existentes. Se deja apuntando
        // a los nuevos por compatibilidad, pero la vista usa las dos de arriba.
        $listaDeProductos = $productosNuevos;

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

        // Reseñas del producto, con nombre y foto del autor. La vista pinta
        // las estrellas a partir de $producto['promedio_estrellas'] (lo trae
        // la consulta del producto) y el promedio lo calcula la BD.
        $listaDeResenias = $this->reseniaModel->listarPorProducto($producto['id_producto']);

        // Si el usuario ya opinó, el formulario se precarga con su reseña.
        $reseniaDelUsuario = null;

        if (usuarioLogueado()) {
            $reseniaDelUsuario = $this->reseniaModel->obtenerDeUsuario(
                (int) $_SESSION['id_usuario'],
                (int) $producto['id_producto']
            );
        }

        require __DIR__ . '/../views/paginaProducto.php';
    }
}
?>