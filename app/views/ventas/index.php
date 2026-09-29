<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa-solid fa-cart-shopping me-2 text-primary"></i>Ventas
        </h1>
        <p class="text-muted small mb-0">Listado histórico de ventas realizadas</p>
    </div>
    <div>
        <a href="<?= URL_BASE ?>/ventas/create" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Nueva Venta
        </a>
    </div>
</div>

<!-- Filtros de búsqueda -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= URL_BASE ?>/ventas" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="fecha_desde" class="form-label small fw-semibold">Fecha Desde</label>
                <input type="date" class="form-control form-control-sm" id="fecha_desde" name="fecha_desde"
                       value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label for="fecha_hasta" class="form-label small fw-semibold">Fecha Hasta</label>
                <input type="date" class="form-control form-control-sm" id="fecha_hasta" name="fecha_hasta"
                       value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label for="id_cliente" class="form-label small fw-semibold">Cliente</label>
                <select class="form-select form-select-sm" id="id_cliente" name="id_cliente">
                    <option value="">-- Todos los clientes --</option>
                    <?php foreach ($clientes as $cli): ?>
                        <option value="<?= $cli['id_cliente'] ?>" <?= ($filtros['id_cliente'] ?? '') == $cli['id_cliente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cli['nombre_apellido']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
                <a href="<?= URL_BASE ?>/ventas" class="btn btn-sm btn-outline-secondary" title="Limpiar filtros">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de ventas -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 80px;" class="text-center"># ID</th>
                        <th style="width: 130px;">Fecha</th>
                        <th>Cliente</th>
                        <th>Registrado por</th>
                        <th class="text-end" style="width: 150px;">Total</th>
                        <th style="width: 100px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ventas)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
                                No se encontraron ventas registradas con los filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $granTotal = 0.0;
                        foreach ($ventas as $v): 
                            $granTotal += (float) ($v['total'] ?? 0);
                        ?>
                            <tr>
                                <td class="text-center fw-bold">#<?= $v['id_venta'] ?></td>
                                <td>
                                    <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                    <?= date('d/m/Y', strtotime($v['fecha'])) ?>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($v['nombre_apellido']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($v['nombre_usuario']) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    $<?= number_format((float) ($v['total'] ?? 0), 2, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= URL_BASE ?>/ventas/show/<?= $v['id_venta'] ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                        <i class="fa-solid fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($ventas)): ?>
                    <tfoot class="table-light fw-bold border-top">
                        <tr>
                            <td colspan="4" class="text-end">Total General (<?= count($ventas) ?> ventas):</td>
                            <td class="text-end text-success fs-6">$<?= number_format($granTotal, 2, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
