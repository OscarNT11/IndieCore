<?php
class UsuarioModel {

    private $conexion;

    public function __construct($conexion) {
            $this->conexion = $conexion;
    }

    // True si el email ya esta registrado en la bd
    public function existeEmail($email) {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->num_rows > 0;
    }

    /* Crea la cuenta en la tabla `usuarios` y devuelve el id generado
        por la BD, o false si algo falla. La contrasenia se guarda con
        password_hash() para no almacenarla en texto plano. */
    public function crearUsuario($nombre, $email, $contrasenia) {
        $stmt = $this->conexion->prepare(
            "INSERT INTO usuarios (nombre, email, contrasenia) VALUES (?, ?, ?)"
        );

        $hash = password_hash($contrasenia, PASSWORD_DEFAULT);

        $stmt->bind_param("sss", $nombre, $email, $hash);

        if (!$stmt->execute()) {
            return false;
        }

        return $this->conexion->insert_id;
    }

    /* Busca un usuario por correo (o por nombre) para el inicio de sesión.
        Devuelve la fila del usuario como array asociativo o null si no existe. */
    public function buscarPorEmail($email) {
        $stmt = $this->conexion->prepare(
            "SELECT id_usuario, nombre, email, contrasenia, foto, rol, suspendida FROM usuarios
                WHERE email = ? OR nombre = ? LIMIT 1"
        );

        $stmt->bind_param("ss", $email, $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc();
    }

    /* Devuelve los datos de un usuario por su id (para la pagina de cuenta).
        Incluye la foto; devuelve null si no existe. */
    public function obtenerPorId($idUsuario) {
        $stmt = $this->conexion->prepare(
            "SELECT id_usuario, nombre, email, contrasenia, foto, rol, suspendida
                FROM usuarios WHERE id_usuario = ? LIMIT 1"
        );

        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->fetch_assoc();
    }

    /* Indica si otro usuario (distinto de $idUsuario) ya usa ese correo. */
    public function existeEmailEnOtro($email, $idUsuario) {
        $stmt = $this->conexion->prepare(
            "SELECT id_usuario FROM usuarios
                WHERE email = ? AND id_usuario <> ? LIMIT 1"
        );

        $stmt->bind_param("si", $email, $idUsuario);
        $stmt->execute();

        $resultado = $stmt->get_result();

        return $resultado->num_rows > 0;
    }

    /* Actualiza el nombre y (si se pasa $rutaFoto) la foto del usuario.
        El correo no se cambia, la cuenta esta ligada */
    public function actualizarPerfil($idUsuario, $nombre, $rutaFoto = null) {

        if ($rutaFoto !== null) {

            $stmt = $this->conexion->prepare(
                "UPDATE usuarios
                    SET nombre = ?, foto = ?
                    WHERE id_usuario = ?"
            );

            $stmt->bind_param("ssi", $nombre, $rutaFoto, $idUsuario);

        } else {

            $stmt = $this->conexion->prepare(
                "UPDATE usuarios
                    SET nombre = ?
                    WHERE id_usuario = ?"
            );

            $stmt->bind_param("si", $nombre, $idUsuario);
        }

        return $stmt->execute();
    }


    /* Cambia la contraseña del usuario, guardándola hasheada. */
    public function actualizarContrasenia($idUsuario, $contrasenia) {
        $stmt = $this->conexion->prepare(
            "UPDATE usuarios SET contrasenia = ? WHERE id_usuario = ?"
        );

        $hash = password_hash($contrasenia, PASSWORD_DEFAULT);

        $stmt->bind_param("si", $hash, $idUsuario);

        return $stmt->execute();
    }

    /* ---------- Panel de administración de usuarios ---------- */

    /* Columnas que usa el listado del panel y el formulario de edición. */
    private $columnasAdmin = "id_usuario, nombre, email, foto, rol, suspendida, fecha_registro";

    /* Listado completo de usuarios para el panel, del más nuevo al más viejo. */
    public function listarTodosAdmin() {

        $sql = "SELECT $this->columnasAdmin
            FROM usuarios
            ORDER BY fecha_registro DESC, id_usuario DESC";

        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            return array();
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    /* Un usuario con los mismos datos que usa el listado del panel. */
    public function obtenerUsuarioAdmin($idUsuario) {

        $stmt = $this->conexion->prepare(
            "SELECT $this->columnasAdmin FROM usuarios WHERE id_usuario = ? LIMIT 1"
        );

        if (!$stmt) {
            return null;
        }

        $idUsuario = (int) $idUsuario;

        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $usuario = $resultado ? $resultado->fetch_assoc() : null;

        $stmt->close();

        return $usuario ?: null;
    }

    /* Cantidad de administradores activos (no suspendidos). Sirve para no
       dejar la tienda sin ningún administrador que pueda entrar al panel. */
    public function contarAdministradoresActivos() {

        $resultado = $this->conexion->query(
            "SELECT COUNT(*) AS total FROM usuarios
                WHERE rol = 'administrador' AND suspendida = 0"
        );

        $fila = $resultado ? $resultado->fetch_assoc() : null;

        return $fila ? (int) $fila['total'] : 0;
    }

    /* Cambia el rol del usuario ('cliente', 'operador' o 'administrador').
       Devuelve true si se guardó. */
    public function cambiarRol($idUsuario, $rol) {

        $rolesValidos = array('cliente', 'operador', 'administrador');

        if (!in_array($rol, $rolesValidos, true)) {
            return false;
        }

        $stmt = $this->conexion->prepare(
            "UPDATE usuarios SET rol = ? WHERE id_usuario = ?"
        );

        if (!$stmt) {
            return false;
        }

        $idUsuario = (int) $idUsuario;

        $stmt->bind_param("si", $rol, $idUsuario);

        return $stmt->execute();
    }

    /* Suspende ($suspendida = 1) o reactiva ($suspendida = 0) la cuenta. */
    public function cambiarSuspension($idUsuario, $suspendida) {

        $stmt = $this->conexion->prepare(
            "UPDATE usuarios SET suspendida = ? WHERE id_usuario = ?"
        );

        if (!$stmt) {
            return false;
        }

        $idUsuario = (int) $idUsuario;
        $suspendida = $suspendida ? 1 : 0;

        $stmt->bind_param("ii", $suspendida, $idUsuario);

        return $stmt->execute();
    }
}
?>