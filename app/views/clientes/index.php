<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-users me-2"></i>Clientes</h1>
    <a href="<?= URL_BASE ?>/clientes/create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo Cliente
    </a>
</div>

<!-- Formulario de búsqueda -->
<form method="GET" action="<?= URL_BASE ?>/clientes" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" class="form-control"
               placeholder="Buscar por nombre/apellido..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="btn btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass"></i> Buscar
        </button>
        <?php if (!empty($_GET['q'])): ?>
            <a href="<?= URL_BASE ?>/clientes" class="btn btn-outline-danger" title="Limpiar búsqueda">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($clientes)): ?>
    <div class="alert alert-info">No hay clientes registrados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nombre / Apellido</th>
                    <th>Localidad</th>
                    <th>Teléfono 1</th>
                    <th>CUIL</th>
                    <th style="width: 130px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= (int) $c['id_cliente'] ?></td>
                    <td><?= htmlspecialchars($c['nombre_apellido']) ?></td>
                    <td><?= htmlspecialchars($c['localidad'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['telefono_1']) ?></td>
                    <td><?= htmlspecialchars($c['cuil'] ?? '—') ?></td>
                    <td class="text-center">
                        <a href="<?= URL_BASE ?>/clientes/edit/<?= (int) $c['id_cliente'] ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="Editar">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a href="<?= URL_BASE ?>/clientes/delete/<?= (int) $c['id_cliente'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           title="Eliminar"
                           onclick="return confirmDelete()">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
