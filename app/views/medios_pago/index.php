<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-credit-card me-2"></i>Medios de Pago</h1>
    <a href="<?= URL_BASE ?>/medios-pago/create" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i>Nuevo Medio de Pago
    </a>
</div>

<?php if (empty($mediosPago)): ?>
    <div class="alert alert-info">No hay medios de pago registrados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Medio de Pago</th>
                    <th style="width: 130px;" class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mediosPago as $mp): ?>
                <tr>
                    <td><?= (int) $mp['id_medio_pago'] ?></td>
                    <td><?= htmlspecialchars($mp['medio_pago']) ?></td>
                    <td class="text-center">
                        <a href="<?= URL_BASE ?>/medios-pago/edit/<?= (int) $mp['id_medio_pago'] ?>"
                           class="btn btn-sm btn-outline-primary me-1" title="Editar">
                            <i class="fa-solid fa-pencil"></i>
                        </a>
                        <a href="<?= URL_BASE ?>/medios-pago/delete/<?= (int) $mp['id_medio_pago'] ?>"
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
