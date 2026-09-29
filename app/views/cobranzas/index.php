<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-hand-holding-dollar me-2"></i>Cobranzas - Cuentas Corrientes</h1>
</div>

<?php if (empty($clientes)): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info me-2"></i>No hay cuentas corrientes pendientes.
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Cliente</th>
                    <th style="width: 160px;" class="text-end">Saldo Total</th>
                    <th style="width: 130px;" class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['nombre_apellido']) ?></td>
                    <td class="text-end fw-semibold text-danger">
                        $ <?= number_format((float) $c['saldo_total'], 2, ',', '.') ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= URL_BASE ?>/cobranzas/cliente/<?= (int) $c['id_cliente'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-eye me-1"></i>Ver Detalle
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
