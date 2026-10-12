<?php
class UsuarioModel {
    private $conexion;
        public function __construct($conexion) {
            $this->conexion = $conexion;
        }

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
           Devuelve la fila del usuario o null si no existe. */
        public function buscarPorEmail($email) {
            $stmt = $this->conexion->prepare(
                "SELECT id_usuario, nombre, email, contrasenia, foto FROM usuarios
                 WHERE email = ? OR nombre = ? LIMIT 1"
            );

            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();

            $resultado = $stmt->get_result();

            return $resultado->fetch_assoc();
        }

        /* Devuelve los datos de un usuario por su id (para la página de cuenta).
           Incluye la foto; devuelve null si no existe. */
        public function obtenerPorId($idUsuario) {
            $stmt = $this->conexion->prepare(
                "SELECT id_usuario, nombre, email, telefono, contrasenia, foto
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
           El correo no se cambia desde aquí: la cuenta queda ligada a su mail. */
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
}
?>