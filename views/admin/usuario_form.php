<?php
/* Formulario de edición de usuario: cambio de rol y suspensión de la cuenta.
   Recibe $usuario, $errores, $titulo y $esCuentaPropia desde
   AdminUsuarioController. */

$roles = [
    'cliente'       => 'Cliente',
    'operador'      => 'Operador',
    'administrador' => 'Administrador',
];
?>

<section class="panel-admin panel-admin--sin-barra">

    <div class="panel-admin__contenido">

        <div class="panel-admin__cabecera">
            <h1><?= escapar($titulo) ?></h1>
            <a class="panel-admin__icono-boton" href="index.php?pagina=admin_usuario" title="Volver a la lista de usuarios">
                <svg><use href="public/img/iconos.svg#icono-mas"></use></svg>
            </a>
        </div>

        <div class="panel-admin__tarjeta">

            <p class="panel-admin__datos">
                <strong><?= escapar($usuario['nombre']) ?></strong><br>
                <?= escapar($usuario['email']) ?>
            </p>

            <?php if ($esCuentaPropia): ?>
                <div class="alert alert-info" role="status">
                    Esta es tu cuenta: no puedes cambiarte el rol ni suspenderla.
                </div>
            <?php endif; ?>

            <?php if (isset($errores['general']) && !$esCuentaPropia): ?>
                <div class="alert alert-danger" role="alert"><?= escapar($errores['general']) ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="rol" class="form-label">Rol:</label>
                    <select name="rol" id="rol" class="form-select" <?= $esCuentaPropia ? 'disabled' : '' ?> required>
                        <?php foreach ($roles as $valor => $nombreRol): ?>
                            <option value="<?= escapar($valor) ?>"
                                <?= ($usuario['rol'] === $valor) ? 'selected' : '' ?>>
                                <?= escapar($nombreRol) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($esCuentaPropia): ?>
                        <input type="hidden" name="rol" value="<?= escapar($usuario['rol']) ?>">
                    <?php endif; ?>
                    <small>Los operadores entran al panel de productos y pedidos; los administradores además gestionan usuarios.</small>
                    <?php if (isset($errores['rol'])): ?>
                        <div class="text-danger"><?= escapar($errores['rol']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="suspendida" id="suspendida" class="form-check-input" value="1"
                           <?= ((int) $usuario['suspendida'] === 1) ? 'checked' : '' ?>
                           <?= $esCuentaPropia ? 'disabled' : '' ?>>
                    <label for="suspendida" class="form-check-label">
                        Cuenta suspendida (no puede iniciar sesión)
                    </label>
                    <?php if ($esCuentaPropia): ?>
                        <input type="hidden" name="suspendida" value="<?= (int) $usuario['suspendida'] ?>">
                    <?php endif; ?>
                    <?php if (isset($errores['suspendida'])): ?>
                        <div class="text-danger"><?= escapar($errores['suspendida']) ?></div>
                    <?php endif; ?>
                </div>

                <?php if (!$esCuentaPropia): ?>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</section>
