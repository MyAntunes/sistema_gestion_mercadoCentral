<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-truck me-2"></i>Proveedores</h1>
    <a href="<?= URL_BASE ?>/proveedores/create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo Proveedor
    </a>
</div>

<!-- Formulario de búsqueda -->
<form method="GET" action="<?= URL_BASE ?>/proveedores" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" class="form-control"
               placeholder="Buscar por razón social..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="btn btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass"></i> Buscar
        </button>
        <?php if (!empty($_GET['q'])): ?>
            <a href="<?= URL_BASE ?>/proveedores" class="btn btn-outline-danger" title="Limpiar búsqueda">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($proveedores)): ?>
    <div class="alert alert-info">No hay proveedores registrados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Razón Social</th>
                    <th>Dirección</th>
                    <th>Teléfono 1</th>
                    <th>CUIT</th>
                    <th style="width: 130px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($proveedores as $p): ?>
                <tr>
                    <td><?= (int) $p['id_proveedor'] ?></td>
                    <td><?= htmlspecialchars($p['razon_social']) ?></td>
                    <td><?= htmlspecialchars($p['direccion'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($p['telefono_1']) ?></td>
                    <td><?= htmlspecialchars($p['cuit'] ?? '—') ?></td>
                    <td class="text-center">
                        <a href="<?= URL_BASE ?>/proveedores/edit/<?= (int) $p['id_proveedor'] ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="Editar">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a href="<?= URL_BASE ?>/proveedores/delete/<?= (int) $p['id_proveedor'] ?>"
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
