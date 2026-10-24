<?php
// controllers/AdminUsuarioController.php
require_once __DIR__ . '/../config/auth.php';

/* Gestión de usuarios del panel: listado, cambio de rol y suspensión /
   reactivación de cuentas. Solo para administradores (un operador no puede
   tocar usuarios). */
class AdminUsuarioController {

    private $conexion;
    private $usuarioModel;

    /* Avisos que el listado muestra tras volver de una acción. */
    private $avisos = array(
        'rol_cliente'     => "El usuario ahora es cliente.",
        'rol_operador'    => "El usuario ahora es operador.",
        'rol_admin'       => "El usuario ahora es administrador.",
        'suspendido'      => "Cuenta suspendida. El usuario no podrá iniciar sesión.",
        'reactivado'      => "Cuenta reactivada. El usuario ya puede iniciar sesión.",
    );

    /* Mensajes de error que el listado muestra si la acción no se pudo hacer. */
    private $erroresAccion = array(
        'propia_cuenta'     => "No puedes cambiar tu propio rol ni suspender tu propia cuenta.",
        'ultimo_admin'      => "No se puede dejar la tienda sin administradores activos.",
        'usuario_invalido'  => "El usuario indicado no existe.",
        'sin_cambios'       => "No se pudo guardar el cambio. Inténtalo de nuevo.",
    );

    public function __construct($conexion) {

        if (!esAdmin()) {
            http_response_code(403);
            require __DIR__ . '/../views/sin_permiso.php';
            exit;
        }

        $this->conexion = $conexion;
        $this->usuarioModel = new UsuarioModel($conexion);
    }

    /* index.php?pagina=admin_usuario */
    public function listar() {

        $usuarios = $this->usuarioModel->listarTodosAdmin();

        $clave = $_GET['aviso'] ?? '';
        $aviso = is_string($clave) && isset($this->avisos[$clave])
            ? $this->avisos[$clave]
            : '';

        $claveError = $_GET['error'] ?? '';
        $error = is_string($claveError) && isset($this->erroresAccion[$claveError])
            ? $this->erroresAccion[$claveError]
            : '';

        $cssExtra = ['public/css/paginaPanel.css'];
        $claseBody = 'body-panel';

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/admin/usuario_lista.php';
        require __DIR__ . '/../views/footer_simple.php';
    }

    /* index.php?pagina=admin_usuario_editar&id=N
       Formulario para cambiar el rol y/o suspender la cuenta. */
    public function editar() {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id <= 0) {
            $this->volverCon('error', 'usuario_invalido');
        }

        $actual = $this->usuarioModel->obtenerUsuarioAdmin($id);

        if (!$actual) {
            $this->volverCon('error', 'usuario_invalido');
        }

        $usuario = array(
            'id_usuario' => (int) $actual['id_usuario'],
            'nombre'     => $actual['nombre'],
            'email'      => $actual['email'],
            'foto'       => $actual['foto'],
            'rol'        => $actual['rol'],
            'suspendida' => (int) $actual['suspendida'],
        );

        $errores = array();
        $esCuentaPropia = ((int) $usuario['id_usuario'] === (int) $_SESSION['id_usuario']);
        $titulo = "Editar usuario";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario = $this->leerFormulario();
            $usuario['id_usuario'] = (int) $id;
            $usuario['nombre'] = $actual['nombre'];
            $usuario['email'] = $actual['email'];
            $usuario['foto'] = $actual['foto'];

            $errores = $this->validar($usuario, $actual, $esCuentaPropia);

            if (empty($errores)) {

                $cambioRol = ((string) $usuario['rol'] !== (string) $actual['rol']);
                $cambioSuspension = ((int) $usuario['suspendida'] !== (int) $actual['suspendida']);

                if (!$cambioRol && !$cambioSuspension) {
                    $errores['general'] = "No hay ningún cambio que guardar.";
                } else {

                    $guardado = true;

                    if ($cambioRol) {
                        $guardado = $this->usuarioModel->cambiarRol($id, $usuario['rol']);
                    }

                    if ($guardado && $cambioSuspension) {
                        $guardado = $this->usuarioModel->cambiarSuspension($id, $usuario['suspendida']);
                    }

                    if (!$guardado) {
                        $errores['general'] = "No se pudieron guardar los cambios. Inténtalo de nuevo.";
                    } else {                        $this->volverCon('aviso', $this->avisoGuardado($usuario));
                    }
                }
            }
        }

        $this->mostrarFormulario($usuario, $errores, $titulo, $esCuentaPropia);
    }

    /* index.php?pagina=admin_usuario_rol&id=N&rol=operador
       Cambia el rol directamente desde el listado. */
    public function cambiarRol() {

        $usuario = $this->usuarioDeLaAccion();

        $rol = isset($_GET['rol']) ? (string) $_GET['rol'] : '';
        $rolesPermitidos = array('cliente', 'operador', 'administrador');

        if (!in_array($rol, $rolesPermitidos, true)) {
            $this->volverCon('error', 'usuario_invalido');
        }

        $motivo = $this->motivoNoPuedeTocar($usuario, $rol, (int) $usuario['suspendida']);

        if ($motivo !== '') {
            $this->volverCon('error', $motivo);
        }

        if (!$this->usuarioModel->cambiarRol($usuario['id_usuario'], $rol)) {
            $this->volverCon('error', 'sin_cambios');
        }

        $this->volverCon('aviso', 'rol_' . $rol);
    }

    /* index.php?pagina=admin_usuario_estado&id=N
       Invierte la suspensión de la cuenta (suspender / reactivar). */
    public function cambiarEstado() {

        $usuario = $this->usuarioDeLaAccion();

        $nuevoEstado = ((int) $usuario['suspendida'] === 1) ? 0 : 1;

        // Se comprueba con el estado al que se va a pasar: suspender al
        // último administrador activo dejaría la tienda sin acceso al panel.
        $motivo = $this->motivoNoPuedeTocar($usuario, $usuario['rol'], $nuevoEstado);

        if ($motivo !== '') {
            $this->volverCon('error', $motivo);
        }

        if (!$this->usuarioModel->cambiarSuspension($usuario['id_usuario'], $nuevoEstado)) {
            $this->volverCon('error', 'sin_cambios');
        }

        $this->volverCon('aviso', $nuevoEstado === 1 ? 'suspendido' : 'reactivado');
    }

    /* Utilidades */

    /* Carga la vista del formulario con sus datos. */
    private function mostrarFormulario($usuario, $errores, $titulo, $esCuentaPropia) {

        $cssExtra = ['public/css/paginaPanel.css'];
        $claseBody = 'body-panel';

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/admin/usuario_form.php';
        require __DIR__ . '/../views/footer_simple.php';
    }

    /* Toma los datos del POST y los deja listos para validar. */
    private function leerFormulario() {
        return array(
            'id_usuario' => null,
            'nombre'     => '',
            'email'      => '',
            'foto'       => null,
            'rol'        => trim((string) ($_POST['rol'] ?? '')),
            'suspendida' => isset($_POST['suspendida']) ? 1 : 0,
        );
    }

    /* Reglas de negocio del formulario. */
    private function validar($usuario, $actual, $esCuentaPropia) {

        $errores = array();

        $rolesPermitidos = array('cliente', 'operador', 'administrador');

        if (!in_array($usuario['rol'], $rolesPermitidos, true)) {
            $errores['rol'] = "El rol seleccionado no es válido.";
        }

        // Un administrador no se puede bajar el rol ni suspender a sí mismo:
        // se quedaría sin acceso al panel de un momento a otro.
        if ($esCuentaPropia) {

            if ($usuario['rol'] !== $actual['rol']) {
                $errores['rol'] = "No puedes cambiar tu propio rol.";
            }

            if ((int) $usuario['suspendida'] !== (int) $actual['suspendida']) {
                $errores['suspendida'] = "No puedes suspender tu propia cuenta.";
            }
        }

        // No dejar la tienda sin ningún administrador que pueda entrar.
        $quitaAdmin = ((int) $actual['suspendida'] === 0)
            && $actual['rol'] === 'administrador'
            && ($usuario['rol'] !== 'administrador' || (int) $usuario['suspendida'] === 1);

        if ($quitaAdmin && !isset($errores['rol']) && !isset($errores['suspendida'])
            && $this->usuarioModel->contarAdministradoresActivos() <= 1) {
            $errores['general'] = "Este es el último administrador activo: no se puede cambiar su rol ni suspenderlo.";
        }

        return $errores;
    }

    /* Usuario pedido por ?id= o redirige al listado si no es válido. */
    private function usuarioDeLaAccion() {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id <= 0) {
            $this->volverCon('error', 'usuario_invalido');
        }

        $usuario = $this->usuarioModel->obtenerUsuarioAdmin($id);

        if (!$usuario) {
            $this->volverCon('error', 'usuario_invalido');
        }

        return $usuario;
    }

    /* Devuelve la clave del error, o '' si la acción sí está permitida.
       $rolNuevo y $suspensionNueva son el estado FINAL que tendría la cuenta. */
    private function motivoNoPuedeTocar($usuario, $rolNuevo, $suspensionNueva) {

        if ((int) $usuario['id_usuario'] === (int) $_SESSION['id_usuario']) {
            return 'propia_cuenta';
        }

        // La cuenta pasaría de ser un administrador activo a no serlo (por
        // bajarle el rol o por suspenderla).
        $eraAdminActivo = ($usuario['rol'] === 'administrador')
            && ((int) $usuario['suspendida'] === 0);

        $sigueAdminActivo = ($rolNuevo === 'administrador') && ((int) $suspensionNueva === 0);

        if ($eraAdminActivo && !$sigueAdminActivo
            && $this->usuarioModel->contarAdministradoresActivos() <= 1) {
            return 'ultimo_admin';
        }

        return '';
    }

    /* Aviso que corresponde al estado guardado desde el formulario. */
    private function avisoGuardado($usuario) {

        if ((int) $usuario['suspendida'] === 1) {
            return 'suspendido';
        }

        if ($usuario['rol'] === 'administrador') {
            return 'rol_admin';
        }

        if ($usuario['rol'] === 'operador') {
            return 'rol_operador';
        }

        return 'rol_cliente';
    }

    /* Vuelve al listado con un aviso o un error. */
    private function volverCon($tipo, $clave) {
        header('Location: index.php?pagina=admin_usuario&' . $tipo . '=' . urlencode($clave));
        exit;
    }
}
