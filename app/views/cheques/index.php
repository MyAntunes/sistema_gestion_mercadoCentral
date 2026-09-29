<?php
$pageTitle = 'Gestión de Cheques';
require_once __DIR__ . '/../layouts/header.php';

// Colores Bootstrap por estado
$badgeClases = [
    'Cartera'    => 'warning',
    'Depositado' => 'info',
    'Cobrado'    => 'success',
    'Rechazado'  => 'danger',
];

// Totales
$totalCheques = count($cheques);
$totalMonto   = array_sum(array_column($cheques, 'monto'));
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0"><i class="fa-solid fa-money-check me-2"></i>Gestión de Cheques</h2>
</div>

<!-- ── Filtros ──────────────────────────────────────────────────────────── -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= URL_BASE ?>/cheques" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="filtroEstado" class="form-label">Estado</label>
                <select id="filtroEstado" name="estado" class="form-select">
                    <option value="">-- Todos --</option>
                    <?php foreach (['Cartera', 'Depositado', 'Cobrado', 'Rechazado'] as $est): ?>
                        <option value="<?= $est ?>"
                            <?= ($filtros['estado'] === $est) ? 'selected' : '' ?>>
                            <?= $est ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filtroDesde" class="form-label">Fecha Pago desde</label>
                <input type="date" id="filtroDesde" name="fecha_desde"
                       class="form-control"
                       value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
            </div>
            <div class="col-md-3">
                <label for="filtroHasta" class="form-label">Fecha Pago hasta</label>
                <input type="date" id="filtroHasta" name="fecha_hasta"
                       class="form-control"
                       value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
                <a href="<?= URL_BASE ?>/cheques" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ── Tabla ─────────────────────────────────────────────────────────────── -->
<?php if (empty($cheques)): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info me-2"></i>No hay cheques registrados.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>N° Cheque</th>
                            <th>Banco</th>
                            <th>Tipo</th>
                            <th>Fecha Emisión</th>
                            <th>Fecha Pago</th>
                            <th class="text-end">Monto</th>
                            <th>Cliente</th>
                            <th>Venta #</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cheques as $ch): ?>
                            <?php
                                $estadoActual   = $ch['estado'];
                                $badgeClase     = $badgeClases[$estadoActual] ?? 'secondary';
                                $estadosTarget  = (new Cheque())->getEstadosPermitidos($estadoActual);
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($ch['numero_cheque']) ?></td>
                                <td><?= htmlspecialchars($ch['banco']) ?></td>
                                <td><?= htmlspecialchars($ch['medio_pago'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ch['fecha_emision'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($ch['fecha_pago'] ?? '—') ?></td>
                                <td class="text-end">
                                    $<?= number_format((float)$ch['monto'], 2, ',', '.') ?>
                                </td>
                                <td><?= htmlspecialchars($ch['cliente'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($ch['id_venta'])): ?>
                                        <a href="<?= URL_BASE ?>/ventas/show/<?= (int)$ch['id_venta'] ?>">
                                            #<?= (int)$ch['id_venta'] ?>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badgeClase ?>">
                                        <?= htmlspecialchars($estadoActual) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($estadosTarget)): ?>
                                        <form method="POST"
                                              action="<?= URL_BASE ?>/cheques/update-estado/<?= (int)$ch['id_cheque'] ?>"
                                              class="d-flex gap-2 align-items-center">
                                            <select name="estado" class="form-select form-select-sm" style="min-width:130px">
                                                <?php foreach ($estadosTarget as $est): ?>
                                                    <option value="<?= $est ?>"><?= $est ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">
                                                <i class="fa-solid fa-rotate me-1"></i>Actualizar
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">Estado final</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totales al pie -->
        <div class="card-footer d-flex gap-4 text-muted small">
            <span><strong>Total cheques:</strong> <?= $totalCheques ?></span>
            <span><strong>Monto total:</strong> $<?= number_format($totalMonto, 2, ',', '.') ?></span>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
