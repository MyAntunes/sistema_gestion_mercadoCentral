<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-users-gear me-2"></i>Usuarios</h1>
    <a href="<?= URL_BASE ?>/usuarios/create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo Usuario
    </a>
</div>

<?php if (empty($usuarios)): ?>
    <div class="alert alert-info">No hay usuarios registrados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th class="text-center">Estado</th>
                    <th>Fecha Creación</th>
                    <th style="width: 210px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <?php $esPropioUsuario = ((int)$u['id_usuario'] === (int)$_SESSION['id_usuario']); ?>
                <tr>
                    <td><?= (int) $u['id_usuario'] ?></td>
                    <td><?= htmlspecialchars($u['nombre_usuario']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php if ($u['rol'] === 'admin'): ?>
                            <span class="badge bg-dark">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Vendedor</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($u['estado'] === 'Activo'): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($u['fecha_creacion'] ?? '—') ?></td>
                    <td class="text-center">
                        <a href="<?= URL_BASE ?>/usuarios/edit/<?= (int) $u['id_usuario'] ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="Editar">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a href="<?= URL_BASE ?>/usuarios/change-password/<?= (int) $u['id_usuario'] ?>"
                           class="btn btn-sm btn-outline-warning me-1" title="Cambiar Contraseña">
                            <i class="fa-solid fa-key"></i>
                        </a>
                        <?php if (!$esPropioUsuario): ?>
                            <a href="<?= URL_BASE ?>/usuarios/toggle-estado/<?= (int) $u['id_usuario'] ?>"
                               class="btn btn-sm <?= $u['estado'] === 'Activo' ? 'btn-outline-secondary' : 'btn-outline-success' ?> me-1"
                               title="<?= $u['estado'] === 'Activo' ? 'Desactivar' : 'Activar' ?>"
                               onclick="return confirm('¿Confirmar cambio de estado?')">
                                <i class="fa-solid <?= $u['estado'] === 'Activo' ? 'fa-toggle-off' : 'fa-toggle-on' ?>"></i>
                            </a>
                            <a href="<?= URL_BASE ?>/usuarios/delete/<?= (int) $u['id_usuario'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               title="Eliminar"
                               onclick="return confirmDelete()">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        <?php else: ?>
                            <span class="text-muted small ms-1" title="Es tu propio usuario">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
