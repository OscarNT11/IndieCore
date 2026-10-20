<?php
// controllers/UsuarioController.php
require_once __DIR__ . '/../models/UsuarioModel.php';

class UsuarioController {
    
    private $conexion;
    private $usuarioModel;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->usuarioModel = new UsuarioModel($conexion);
    }

    public function registro() {
        
        $errores = [];
        $nombre = '';
        $email = '';
        $mensajeOk = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');

            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';

            // Comprobar longitud
            if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 50) {
                $errores[] = "El nombre debe tener entre 2 y 50 caracteres.";
            }

            // Comprobar caracteres
            if (!nombreUsuarioValido($nombre)) {
                $errores[] = "El nombre de usuario solo puede tener letras, números, espacios y los signos _ - .";
            }


            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El correo electrónico no es válido.";
            }
            elseif ($this->usuarioModel->existeEmail($email)) {
                $errores[] = "Ya existe una cuenta con ese correo.";
            }

            // Validar contrasenias por tamanio y similitud
            if (strlen($password) < 8) {
                $errores[] = "La contraseña debe tener al menos 8 caracteres.";
            }
            if ($password !== $password2) {
                $errores[] = "Las contraseñas no coinciden.";
            }

            // Si no hay errores se procede a crear al usuario
            if (empty($errores)) {
                $idUsuario = $this->usuarioModel->crearUsuario($nombre, $email, $password);

                if ($idUsuario === false) {
                    $errores[] = "No se pudo crear la cuenta. Inténtalo de nuevo.";
                } else {
                    $_SESSION['id_usuario'] = $idUsuario;
                    $_SESSION['nombre_usuario'] = $nombre;
                    $_SESSION['email_usuario'] = $email;
                    // Toda cuenta nueva nace con rol 'cliente' (el DEFAULT de
                    // la tabla). El rol se cambia después desde phpMyAdmin y,
                    // al volver a iniciar sesión, se carga en $_SESSION['rol'].
                    $_SESSION['rol'] = 'cliente';

                    // Vuelve a la MISMA página donde estaba el usuario
                    // (la manda el campo oculto "volver" del modal).
                    header("Location: index.php?pagina=" . $this->paginaVolver() . "&registro=ok");
                    exit;
                }
            }
        }

        /* La vista se carga SIEMPRE (GET incluido). Antes este require estaba
        dentro del if de POST, así que al abrir index.php?pagina=registro
        con GET no se incluía nada y se veía una página en blanco. */
        require __DIR__ . '/../views/registro.php';
    }


    /* Muestra (y procesa) el formulario de inicio de sesión.
       index.php llama a este método para ?pagina=login; sin él la URL
       lanzaba un "Call to undefined method" y la página quedaba vacía. */
    public function login() {

        $errores = [];
        $usuarioIngresado = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuarioIngresado = trim($_POST['usuario'] ?? '');
            $password = $_POST['password'] ?? '';


            // Verifica que ningun campo este vacio
            if ($usuarioIngresado === '') {
                $errores[] = "Ingresa tu nombre de usuario o correo.";
            }
            if ($password === '') {
                $errores[] = "Ingresa tu contraseña.";
            }

            if (empty($errores)) {
                // Se busca la cuenta por correo o por nombre de usuario.
                $usuario = $this->usuarioModel->buscarPorEmail($usuarioIngresado);

                if (!$usuario || !password_verify($password, $usuario['contrasenia'])) {
                    $errores[] = "Usuario o contraseña incorrectos.";
                } else {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre'];
                    $_SESSION['email_usuario'] = $usuario['email'];
                    $_SESSION['foto_usuario'] = $usuario['foto'] ?? '';

                    // El rol (cliente / administrador / operador) se guarda en
                    // la sesión para que el header y las páginas protegidas
                    // sepan qué puede hacer este usuario.
                    $_SESSION['rol'] = $usuario['rol'] ?? 'cliente';

                    // Vuelve a la MISMA página donde estaba el usuario
                    // (la manda el campo oculto "volver" del modal).
                    header("Location: index.php?pagina=" . $this->paginaVolver() . "&login=ok");
                    exit;
                }
            }
        }

        require __DIR__ . '/../views/login.php';
    }


    /* Página "Mi cuenta": muestra y actualiza los datos del usuario logueado,
       incluida la foto de perfil. Solo accesible con la sesión iniciada. */
    public function cuenta() {

        // Si no hay sesión, no hay cuenta que mostrar: se vuelve al inicio.
        if (!usuarioLogueado()) {
            header("Location: index.php?pagina=home");
            exit;
        }

        $idUsuario = (int) $_SESSION['id_usuario'];

        $errores = [];
        $mensajeOk = '';

        // Datos del usuario tal como están guardados ahora mismo.
        $usuario = $this->usuarioModel->obtenerPorId($idUsuario);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre               = trim($_POST['nombre'] ?? '');
            $correoConfirmacion   = trim($_POST['email_confirmacion'] ?? '');
            $password             = $_POST['password'] ?? '';
            $password2            = $_POST['password2'] ?? '';

            // ---------- 1. Confirmación del correo ----------
            // Para cambiar el nombre o la contraseña hay que escribir el
            // correo de la cuenta. Si no coincide, no se cambia nada.
            // VALIDACION POR GMAIL o CONTRASENIA
            $correoConfirmado = ($correoConfirmacion !== '')
                && (strcasecmp($correoConfirmacion, $usuario['email']) === 0);

            // ---------- 2. Validación ----------
            $quiereCambiarPassword = ($password !== '' || $password2 !== '');

            if ($nombre !== '' && (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 50)) {
                $errores[] = "El nombre debe tener entre 2 y 50 caracteres.";
            }

            // Misma comprobación que en el registro: el nombre no puede
            // contener código ni signos raros (evita XSS almacenado).
            if ($nombre !== '' && !nombreUsuarioValido($nombre)) {
                $errores[] = "El nombre de usuario solo puede tener letras, números, espacios y los signos _ - .";
            }

            if ($quiereCambiarPassword) {

                if (strlen($password) < 8) {
                    $errores[] = "La nueva contraseña debe tener al menos 8 caracteres.";
                }

                if ($password !== $password2) {
                    $errores[] = "Las contraseñas no coinciden.";
                }
            }

            // ---------- 3. Foto de perfil (se puede subir sin confirmar) ----------
            $rutaFotoNueva = null;

            if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

                $resultadoFoto = $this->guardarFotoPerfil($_FILES['foto']);

                if (isset($resultadoFoto['error'])) {
                    $errores[] = $resultadoFoto['error'];
                } else {
                    $rutaFotoNueva = $resultadoFoto['ruta'];
                }
            }

            // La confirmación del correo SOLO hace falta si se quiere cambiar
            // el nombre o la contraseña. Subir solo una foto no la necesita.
            $quiereCambiarNombre = ($nombre !== '' && $nombre !== $usuario['nombre']);

            if (!$correoConfirmado && ($quiereCambiarNombre || $quiereCambiarPassword)) {
                $errores[] = "Escribe tu correo electrónico para confirmar los cambios de nombre o contraseña.";
            }

            // Si no se cambió nada, se avisa en vez de decir que se guardó
            // todo bien sin haber hecho nada.
            $hayCambios = ($rutaFotoNueva !== null) || $quiereCambiarNombre || $quiereCambiarPassword;

            if (empty($errores) && !$hayCambios) {
                $errores[] = "No hay ningún cambio que guardar.";
            }

            // ---------- 4. Guardado ----------
            if (empty($errores)) {

                // El nombre solo se cambia si se confirmó el correo; si no,
                // se conserva el actual.
                $nombreFinal = ($correoConfirmado && $nombre !== '')
                    ? $nombre
                    : $usuario['nombre'];

                // Si no hay foto nueva se mantiene la que ya tenía.
                if ($rutaFotoNueva === null) {
                    $rutaFotoNueva = $usuario['foto'];
                }

                $actualizado = $this->usuarioModel->actualizarPerfil(
                    $idUsuario, $nombreFinal, $rutaFotoNueva
                );

                if (!$actualizado) {
                    $errores[] = "No se pudieron guardar los cambios.";
                } else {

                    // La contraseña solo cambia si se confirmó el correo y
                    // el usuario escribió una nueva.
                    if ($correoConfirmado && $quiereCambiarPassword) {
                        $this->usuarioModel->actualizarContrasenia($idUsuario, $password);
                    }

                    // Se refresca la sesión para que el header muestre el
                    // nombre y la foto nuevos sin volver a iniciar sesión.
                    $_SESSION['nombre_usuario'] = $nombreFinal;
                    $_SESSION['foto_usuario']   = $rutaFotoNueva ?? '';

                    $mensajeOk = "Los cambios se guardaron correctamente.";

                    // Se recargan los datos que se muestran en la vista.
                    $usuario = $this->usuarioModel->obtenerPorId($idUsuario);
                }
            }
        }

        require __DIR__ . '/../views/cuenta.php';
    }

    /* Valida y mueve la imagen subida a public/img/perfiles/.
       Devuelve ['ruta' => ...] o ['error' => mensaje]. */
    private function guardarFotoPerfil($archivo) {

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return array('error' => "No se pudo subir la imagen.");
        }

        $tamanoMaximo = 2 * 1024 * 1024; // 2 MB

        if ($archivo['size'] > $tamanoMaximo) {
            return array('error' => "La imagen no puede pesar más de 2 MB.");
        }

        // El tipo real se comprueba con getimagesize(), no con la extensión
        // que manda el navegador (esa se puede falsificar).
        $infoImagen = @getimagesize($archivo['tmp_name']);

        if ($infoImagen === false) {
            return array('error' => "El archivo no es una imagen válida.");
        }

        $extensionesPermitidas = array(
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG  => 'png',
            IMAGETYPE_GIF  => 'gif',
            IMAGETYPE_WEBP => 'webp',
        );

        if (!isset($extensionesPermitidas[$infoImagen[2]])) {
            return array('error' => "Formato no permitido. Usa JPG, PNG, GIF o WEBP.");
        }

        $extension = $extensionesPermitidas[$infoImagen[2]];

        $carpetaDestino = __DIR__ . '/../public/img/perfiles/';

        if (!is_dir($carpetaDestino)) {
            mkdir($carpetaDestino, 0755, true);
        }

        // Nombre único para no pisar fotos de otros usuarios ni exponer
        // el nombre original del archivo.
        $nombreArchivo = 'perfil_' . uniqid() . '.' . $extension;

        $destino = $carpetaDestino . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            return array('error' => "No se pudo guardar la imagen en el servidor.");
        }

        // Ruta relativa que se guarda en la BD y usa la vista.
        return array('ruta' => 'public/img/perfiles/' . $nombreArchivo);
    }
    /* Devuelve la página a la que volver tras iniciar sesión o registrarse.
       La manda el campo oculto "volver" del modal; si no llega, se usa la
       página actual como respaldo. Se sanea para no aceptar rutas ni URLs
       externas en un header Location. */
    private function paginaVolver() {

        $volver = isset($_POST['volver']) ? (string) $_POST['volver'] : '';

        $volver = preg_replace('/[^a-zA-Z0-9_-]/', '', $volver);

        return $volver === '' ? paginaActual() : $volver;
    }

    /* Cierra la sesión del usuario y vuelve a la página de inicio.
       index.php llama a este método para ?pagina=logout. */
    public function logout() {

        $_SESSION = array();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header("Location: index.php?pagina=home");
        exit;
    }
}
