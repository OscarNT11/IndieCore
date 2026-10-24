<?php
// controllers/ReseniaController.php
require_once __DIR__ . '/../models/ReseniaModel.php';

/* Atiende el formulario de reseñas de la página de producto.
   El formulario envía por POST a index.php?pagina=resenias los campos
   id_producto, calificacion (1-5) y comentario. Después de guardar
   (o fallar) se vuelve a la ficha del producto. */
class ReseniaController {

    private $conexion;
    private $reseniaModel;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->reseniaModel = new ReseniaModel($conexion);
    }

    public function guardar() {

        // La ida y vuelta con el modal de login pierde el POST: si alguien
        // entra así, se le manda de nuevo al producto.
        if (!usuarioLogueado()) {
            header("Location: index.php?pagina=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?pagina=catalogo");
            exit;
        }

        $idProducto = isset($_POST['id_producto']) ? (int) $_POST['id_producto'] : 0;

        if ($idProducto <= 0 || !$this->reseniaModel->productoResenable($idProducto)) {
            header("Location: index.php?pagina=catalogo");
            exit;
        }

        // Se valida también en el servidor: el navegador puede mandar
        // cualquier cosa en el campo de estrellas.
        $calificacion = isset($_POST['calificacion']) ? (int) $_POST['calificacion'] : 0;
        $comentario = isset($_POST['comentario']) ? trim((string) $_POST['comentario']) : '';

        if ($calificacion < 1 || $calificacion > 5) {
            $this->volverAlProducto($idProducto, 'estrellas');
        }

        if ($comentario === '' || mb_strlen($comentario) > 1000) {
            $this->volverAlProducto($idProducto, 'comentario');
        }

        $guardada = $this->reseniaModel->guardar(
            (int) $_SESSION['id_usuario'],
            $idProducto,
            $calificacion,
            $comentario
        );

        $this->volverAlProducto($idProducto, $guardada ? 'ok' : 'error');
    }

    /* Vuelve a la ficha del producto y deja anclada la sección de reseñas
       (#opiniones), con un aviso del resultado en ?resenia=. */
    private function volverAlProducto($idProducto, $resultado) {

        header(
            "Location: index.php?pagina=producto&id=" . (int) $idProducto
            . "&resenia=" . urlencode($resultado)
            . "#opiniones"
        );
        exit;
    }
}
?>
