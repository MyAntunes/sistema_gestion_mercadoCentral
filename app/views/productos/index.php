<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-box me-2"></i>Productos</h1>
    <a href="<?= URL_BASE ?>/productos/create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo Producto
    </a>
</div>

<!-- Formulario de búsqueda -->
<form method="GET" action="<?= URL_BASE ?>/productos" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" class="form-control"
               placeholder="Buscar por nombre o especie..."
               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="btn btn-outline-secondary">
            <i class="fa-solid fa-magnifying-glass"></i> Buscar
        </button>
        <?php if (!empty($_GET['q'])): ?>
            <a href="<?= URL_BASE ?>/productos" class="btn btn-outline-danger" title="Limpiar búsqueda">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<?php if (empty($productos)): ?>
    <div class="alert alert-info">No hay productos registrados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nombre</th>
                    <th>Especie</th>
                    <th class="text-end">Precio Costo</th>
                    <th class="text-end">Precio Venta</th>
                    <th>Proveedor</th>
                    <th style="width: 130px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= (int) $p['id_producto'] ?></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['especie'] ?? '—') ?></td>
                    <td class="text-end">$&nbsp;<?= number_format((float) $p['precio_costo'], 2, ',', '.') ?></td>
                    <td class="text-end">$&nbsp;<?= number_format((float) $p['precio_venta'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($p['razon_social']) ?></td>
                    <td class="text-center">
                        <a href="<?= URL_BASE ?>/productos/edit/<?= (int) $p['id_producto'] ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="Editar">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a href="<?= URL_BASE ?>/productos/delete/<?= (int) $p['id_producto'] ?>"
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
