<?php
/* Listado de usuarios del panel. Recibe $usuarios y los mensajes
   $aviso / $error desde AdminUsuarioController. */

$etiquetasRol = [
    'cliente'       => 'Cliente',
    'operador'      => 'Operador',
    'administrador' => 'Administrador',
];

$idPropio = (int) ($_SESSION['id_usuario'] ?? 0);
?>

<section class="panel-admin panel-admin--sin-barra">

    <div class="panel-admin__contenido panel-productos">

    <div class="panel-admin__cabecera">
        <h1>Lista de usuarios</h1>
    </div>

    <p class="panel-productos__acciones">
        <a href="index.php?pagina=panel">Volver al panel de gestión</a>
    </p>

    <?php if ($aviso !== ''): ?>
        <div class="alert alert-success" role="status"><?= escapar($aviso) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger" role="alert"><?= escapar($error) ?></div>
    <?php endif; ?>

    <?php if (empty($usuarios)): ?>
        <p>No hay usuarios registrados.</p>
    <?php else: ?>
        <div class="panel-productos__tabla"><table class="table table-striped">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <?php
                        $idUsuario = (int) $usuario['id_usuario'];
                        $rol = $usuario['rol'];
                        $suspendida = ((int) $usuario['suspendida'] === 1);
                        $esPropio = ($idUsuario === $idPropio);
                    ?>
                    <tr>
                        <td data-etiqueta="Usuario"><?= escapar($usuario['nombre']) ?><?= $esPropio ? ' (vos)' : '' ?></td>
                        <td data-etiqueta="Correo"><?= escapar($usuario['email']) ?></td>
                        <td data-etiqueta="Rol">
                            <span class="rol rol-<?= escapar($rol) ?>">
                                <?= escapar($etiquetasRol[$rol] ?? $rol) ?>
                            </span>
                        </td>
                        <td data-etiqueta="Estado">
                            <?php if ($suspendida): ?>
                                <span class="stock stock-agotado">suspendida</span>
                            <?php else: ?>
                                <span class="stock stock-normal">activa</span>
                            <?php endif; ?>
                        </td>
                        <td data-etiqueta="Registro"><?= escapar(date('d/m/Y', strtotime($usuario['fecha_registro']))) ?></td>
                        <td data-etiqueta="Acciones">
                            <a href="index.php?pagina=admin_usuario_editar&id=<?= $idUsuario ?>" class="btn btn-sm btn-warning">
                                Editar
                            </a>

                            <?php // Las acciones rápidas no se ofrecen sobre la propia cuenta. ?>
                            <?php if (!$esPropio): ?>
                                <?php if ($rol !== 'administrador'): ?>
                                    <a href="index.php?pagina=admin_usuario_rol&id=<?= $idUsuario ?>&rol=administrador" class="btn btn-sm">
                                        Hacer admin
                                    </a>
                                <?php endif; ?>

                                <?php if ($rol !== 'operador'): ?>
                                    <a href="index.php?pagina=admin_usuario_rol&id=<?= $idUsuario ?>&rol=operador" class="btn btn-sm">
                                        Hacer operador
                                    </a>
                                <?php endif; ?>

                                <?php if ($suspendida): ?>
                                    <a href="index.php?pagina=admin_usuario_estado&id=<?= $idUsuario ?>" class="btn btn-sm btn-primary">
                                        Reactivar
                                    </a>
                                <?php else: ?>
                                    <a href="index.php?pagina=admin_usuario_estado&id=<?= $idUsuario ?>" class="btn btn-sm btn-danger">
                                        Suspender
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table></div>
    <?php endif; ?>

    </div>
</section>
