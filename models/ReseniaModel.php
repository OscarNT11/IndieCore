<?php
/* Reseñas de los productos: estrellas (1 a 5) + comentario.
   Cada usuario puede dejar UNA sola reseña por producto: si vuelve a
   enviar el formulario, su reseña anterior se actualiza (ver guardar()). */
class ReseniaModel {

    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /* Columnas del promedio, listas para meter en un SELECT que tenga la
       tabla `productos` con el alias p. Devuelve el promedio SIN redondear
       (0 si el producto todavía no tiene reseñas) y la cantidad de reseñas.
       El redondeo se hace al mostrarlo: si se redondea acá, dos productos
       con promedios reales distintos (4.25 y 4.34) quedan empatados y el
       orden de "Destacados" sale mal. */
    public static function sqlPromedio($aliasProducto = 'p') {

        return "COALESCE(
                    (SELECT AVG(r.calificacion) FROM resenias r
                        WHERE r.id_producto = $aliasProducto.id_producto),
                    0
                ) AS promedio_estrellas,
                (SELECT COUNT(*) FROM resenias r
                        WHERE r.id_producto = $aliasProducto.id_producto
                ) AS total_resenias";
    }

    /* Todas las reseñas de un producto, de la más reciente a la más vieja,
       con el nombre y la foto del usuario que la escribió (la vista los
       necesita para mostrar quién opinó). */
    public function listarPorProducto($idProducto) {

        $idProducto = (int) $idProducto;

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT r.id_resenia, r.calificacion, r.comentario, r.fecha,
                    u.nombre AS nombre_usuario, u.foto AS foto_usuario
             FROM resenias r
             INNER JOIN usuarios u ON u.id_usuario = r.id_usuario
             WHERE r.id_producto = ?
             ORDER BY r.fecha DESC, r.id_resenia DESC"
        );

        if (!$stmt) {
            return array();
        }

        mysqli_stmt_bind_param($stmt, "i", $idProducto);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        $listaResenias = $resultado ? mysqli_fetch_all($resultado, MYSQLI_ASSOC) : array();

        mysqli_stmt_close($stmt);

        return $listaResenias;
    }

    /* Reseña que ese usuario ya dejó en ese producto (o null si todavía no
       opinó). Se usa para precargar el formulario. */
    public function obtenerDeUsuario($idUsuario, $idProducto) {

        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT id_resenia, calificacion, comentario, fecha
             FROM resenias
             WHERE id_usuario = ? AND id_producto = ?
             LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        mysqli_stmt_bind_param($stmt, "ii", $idUsuario, $idProducto);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $resenia = $resultado ? mysqli_fetch_assoc($resultado) : null;

        mysqli_stmt_close($stmt);

        return $resenia ?: null;
    }

    /* Crea o actualiza la reseña del usuario en ese producto.
       La calificación se fuerza al rango 1-5. Devuelve true si se guardó. */
    public function guardar($idUsuario, $idProducto, $calificacion, $comentario) {

        $idUsuario = (int) $idUsuario;
        $idProducto = (int) $idProducto;
        $calificacion = (int) $calificacion;

        if ($idUsuario <= 0 || $idProducto <= 0 || $calificacion < 1 || $calificacion > 5) {
            return false;
        }

        $comentario = trim((string) $comentario);

        $reseniaActual = $this->obtenerDeUsuario($idUsuario, $idProducto);

        if ($reseniaActual) {

            $stmt = mysqli_prepare(
                $this->conn,
                "UPDATE resenias
                    SET calificacion = ?, comentario = ?, fecha = NOW()
                    WHERE id_resenia = ?"
            );

            if (!$stmt) {
                return false;
            }

            $idResenia = (int) $reseniaActual['id_resenia'];

            mysqli_stmt_bind_param($stmt, "isi", $calificacion, $comentario, $idResenia);

        } else {

            $stmt = mysqli_prepare(
                $this->conn,
                "INSERT INTO resenias (comentario, calificacion, fecha, id_usuario, id_producto)
                 VALUES (?, ?, NOW(), ?, ?)"
            );

            if (!$stmt) {
                return false;
            }

            mysqli_stmt_bind_param($stmt, "siii", $comentario, $calificacion, $idUsuario, $idProducto);
        }

        $ejecutado = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);

        return $ejecutado;
    }

    /* Verifica que el producto exista y esté activo antes de aceptar una
       reseña (no se opina de un producto borrado). */
    public function productoResenable($idProducto) {

        $idProducto = (int) $idProducto;

        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT id_producto FROM productos WHERE id_producto = ? AND activo = 1 LIMIT 1"
        );

        if (!$stmt) {
            return false;
        }

        mysqli_stmt_bind_param($stmt, "i", $idProducto);
        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);
        $existe = $resultado && mysqli_num_rows($resultado) > 0;

        mysqli_stmt_close($stmt);

        return $existe;
    }
}
?>
