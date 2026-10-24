<?php
// controllers/AdminProductoController.php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/imagenes.php';

/* ABM de productos del panel de gestión: listado, alta, modificación y
   baja/alta lógica. Solo para administradores y operadores. */
class AdminProductoController {

    private $conexion;
    private $productoModel;

    /* Avisos que el listado muestra tras volver de una acción. */
    private $avisos = array(
        'creado'      => "Producto creado exitosamente.",
        'editado'     => "Producto actualizado exitosamente.",
        'desactivado' => "Producto desactivado exitosamente.",
        'reactivado'  => "Producto reactivado exitosamente.",
    );

    public function __construct($conexion) {

        if (!esGestor()) {
            redirigir('login');
        }

        $this->conexion = $conexion;
        $this->productoModel = new ProductoModel($conexion);
    }

    /* index.php?pagina=admin_producto */
    public function listar() {

        $productos = $this->productoModel->listarTodosAdmin();

        $clave = $_GET['aviso'] ?? '';
        $aviso = is_string($clave) && isset($this->avisos[$clave])
            ? $this->avisos[$clave]
            : '';

        $cssExtra = ['public/css/paginaPanel.css'];
        $claseBody = 'body-panel';

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/admin/producto_lista.php';
        require __DIR__ . '/../views/footer_simple.php';
    }

    /* index.php?pagina=admin_producto_crear */
    public function crear() {

        $producto = $this->productoVacio();
        $categorias = $this->productoModel->listarCategorias();
        $errores = array();
        $titulo = "Nuevo producto";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $producto = $this->leerFormulario();

            $errores = $this->validar($producto, $categorias);

            if (empty($errores)) {

                $resultadoImagen = $this->guardarFoto(null);

                if (isset($resultadoImagen['error'])) {
                    $errores['foto'] = $resultadoImagen['error'];
                } else {

                    $producto['foto'] = $resultadoImagen['ruta'];

                    // bind_param() recibe los valores por referencia: el array
                    // se copia para que el INSERT lea el estado final de todos
                    // los campos, incluida la foto.
                    // La columna `foto` guarda solo el nombre del archivo; las
                    // vistas le anteponen public/img/ImagenesProductos/.
                    $idCreado = $this->productoModel->crear($this->paraBaseDeDatos($producto));

                    if ($idCreado === false) {
                        eliminarImagenGuardada($resultadoImagen['ruta']);
                        $errores['general'] = "No se pudo crear el producto. Revisa los datos e inténtalo de nuevo.";
                    } else {
                        header('Location: index.php?pagina=admin_producto&aviso=creado');
                        exit;
                    }
                }
            }
        }

        $this->mostrarFormulario($producto, $categorias, $errores, $titulo, true);
    }

    /* index.php?pagina=admin_producto_editar&id=N */
    public function editar() {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $actual = ($id && $id > 0) ? $this->productoModel->obtenerProductoAdmin($id) : null;

        if (!$actual) {
            header('Location: index.php?pagina=admin_producto');
            exit;
        }

        $producto = $this->productoDeFila($actual);
        $categorias = $this->productoModel->listarCategorias();
        $errores = array();
        $titulo = "Editar producto";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $producto = $this->leerFormulario();
            $producto['id_producto'] = (int) $id;
            $producto['foto_actual'] = $actual['imagen'];

            $errores = $this->validar($producto, $categorias);

            if (empty($errores)) {

                $producto['foto'] = $actual['imagen'];

                // Solo se reemplaza la imagen si se subió una nueva. Se guarda
                // primero (esto borra la anterior) y después se usa la ruta
                // nueva, para no borrar el archivo recién subido.
                if (hayArchivoImagen($_FILES['foto'] ?? null)) {

                    $fotoAnterior = $actual['imagen'];

                    $resultadoImagen = guardarImagenSubida($_FILES['foto'], 'producto', 'producto');

                    if (isset($resultadoImagen['error'])) {
                        $errores['foto'] = $resultadoImagen['error'];
                    } else {

                        $producto['foto'] = $resultadoImagen['ruta'];

                        if ($fotoAnterior) {
                            eliminarImagenGuardada(carpetaImagenProductos() . basename($fotoAnterior));
                        }
                    }
                }

                if (empty($errores)) {

                    $modificado = $this->productoModel->modificar($this->paraBaseDeDatos($producto));

                    if (!$modificado) {
                        $errores['general'] = "No se pudo guardar el producto. Inténtalo de nuevo.";
                    } else {
                        header('Location: index.php?pagina=admin_producto&aviso=editado');
                        exit;
                    }
                }
            }
        }

        $this->mostrarFormulario($producto, $categorias, $errores, $titulo, false);
    }

    /* index.php?pagina=admin_producto_estado&id=N
       Invierte el estado activo/inactivo del producto (baja lógica). */
    public function cambiarEstado() {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $actual = ($id && $id > 0) ? $this->productoModel->obtenerProductoAdmin($id) : null;

        if (!$actual) {
            header('Location: index.php?pagina=admin_producto');
            exit;
        }

        $nuevoEstado = ((int) $actual['activo'] === 1) ? 0 : 1;

        $this->productoModel->cambiarEstado($id, $nuevoEstado);

        $aviso = $nuevoEstado === 1 ? 'reactivado' : 'desactivado';

        header('Location: index.php?pagina=admin_producto&aviso=' . $aviso);
        exit;
    }

    /* Utilidades */

    /* Carga la vista del formulario con sus datos. */
    private function mostrarFormulario($producto, $categorias, $errores, $titulo, $esAlta) {

        $cssExtra = ['public/css/paginaPanel.css'];
        $claseBody = 'body-panel';

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/admin/producto_form.php';
        require __DIR__ . '/../views/footer_simple.php';
    }

    /* Producto con los valores por defecto del alta. */
    private function productoVacio() {
        return array(
            'id_producto'  => null,
            'nombre'       => '',
            'marca'        => '',
            'informacion'  => '',
            'foto'         => null,
            'foto_actual'  => null,
            'stock'        => 0,
            'stock_minimo' => 0,
            'precio'       => 0.00,
            'id_categoria' => null,
            'activo'       => 1,
        );
    }

    /* Convierte una fila de la BD al formato que espera el formulario. */
    private function productoDeFila($fila) {
        return array(
            'id_producto'  => (int) $fila['id_producto'],
            'nombre'       => $fila['nombre'],
            'marca'        => $fila['marca'],
            'informacion'  => $fila['informacion'],
            'foto'         => $fila['imagen'],
            'foto_actual'  => $fila['imagen'],
            'stock'        => (int) $fila['stock'],
            'stock_minimo' => (int) $fila['stock_minimo'],
            'precio'       => $fila['precio'],
            'id_categoria' => (int) $fila['id_categoria'],
            'activo'       => (int) $fila['activo'],
        );
    }

    /* Copia los datos del producto al formato que guarda la base: la foto
       como nombre de archivo (sin carpeta) y en un array propio, porque
       bind_param() recibe los valores por referencia. */
    private function paraBaseDeDatos($producto) {

        $datos = array_merge(array(), $producto);

        $datos['foto'] = basename((string) $datos['foto']);

        return $datos;
    }

    /* Toma los datos del POST y los deja listos para validar. */
    private function leerFormulario() {
        return array(
            'id_producto'  => null,
            'nombre'       => trim($_POST['nombre'] ?? ''),
            'marca'        => trim($_POST['marca'] ?? ''),
            'informacion'  => trim($_POST['informacion'] ?? ''),
            'foto'         => null,
            'foto_actual'  => null,
            'stock'        => trim($_POST['stock'] ?? ''),
            'stock_minimo' => trim($_POST['stock_minimo'] ?? ''),
            'precio'       => trim($_POST['precio'] ?? ''),
            'id_categoria' => $_POST['id_categoria'] ?? '',
            'activo'       => isset($_POST['activo']) ? 1 : 0,
        );
    }

    /* Valida los datos del formulario. Devuelve los errores por campo. */
    private function validar($producto, $categorias) {

        $errores = array();

        $nombre = $producto['nombre'];

        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 255) {
            $errores['nombre'] = "El nombre debe tener entre 3 y 255 caracteres.";
        }

        if ($producto['marca'] === '') {
            $errores['marca'] = "Indica la marca del producto.";
        }

        if (mb_strlen($producto['informacion']) < 10) {
            $errores['informacion'] = "La información debe tener al menos 10 caracteres.";
        }

        $precio = filter_var($producto['precio'], FILTER_VALIDATE_FLOAT);

        if ($precio === false || $precio < 0 || $precio > 999999.99) {
            $errores['precio'] = "El precio debe ser un número válido entre 0 y 999999.99.";
        }

        $stock = filter_var($producto['stock'], FILTER_VALIDATE_INT);

        if ($stock === false || $stock < 0 || $stock > 999999) {
            $errores['stock'] = "El stock debe ser un número entero mayor o igual a 0.";
        }

        $stockMinimo = filter_var($producto['stock_minimo'], FILTER_VALIDATE_INT);

        if ($stockMinimo === false || $stockMinimo < 0 || $stockMinimo > 999999) {
            $errores['stock_minimo'] = "El stock mínimo debe ser un número entero mayor o igual a 0.";
        }

        $idsValidos = array_map('intval', array_column($categorias, 'id_categoria'));

        if (!in_array((int) $producto['id_categoria'], $idsValidos, true)) {
            $errores['id_categoria'] = "La categoría seleccionada no es válida.";
        }

        return $errores;
    }

    /* Guarda la foto del alta. Devuelve ['ruta' => ...] o ['error' => mensaje]. */
    private function guardarFoto($fotoActual) {

        if (!hayArchivoImagen($_FILES['foto'] ?? null)) {

            if (!$fotoActual) {
                return array('error' => "Selecciona una imagen para el producto.");
            }

            return array('ruta' => $fotoActual);
        }

        $resultado = guardarImagenSubida($_FILES['foto'], 'producto', 'producto');

        if (isset($resultado['error'])) {
            return $resultado;
        }

        if ($fotoActual) {
            eliminarImagenGuardada(carpetaImagenProductos() . basename($fotoActual));
        }

        return $resultado;
    }
}