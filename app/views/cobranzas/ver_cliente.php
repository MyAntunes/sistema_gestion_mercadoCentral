<?php include __DIR__ . '/../layouts/header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= URL_BASE ?>/cobranzas">Cobranzas</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= htmlspecialchars($cliente['nombre_apellido']) ?>
        </li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">
        <i class="fa-solid fa-user-clock me-2"></i>
        Cuenta Corriente — <?= htmlspecialchars($cliente['nombre_apellido']) ?>
    </h1>
</div>

<!-- Resumen saldo total -->
<?php if ($saldoTotal > 0): ?>
    <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
        <i class="fa-solid fa-circle-exclamation fa-lg"></i>
        <span>
            Saldo total adeudado:
            <strong>$ <?= number_format($saldoTotal, 2, ',', '.') ?></strong>
        </span>
    </div>
<?php else: ?>
    <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
        <i class="fa-solid fa-circle-check fa-lg"></i>
        <span>Este cliente no tiene saldo pendiente.</span>
    </div>
<?php endif; ?>

<?php if (empty($cuentas)): ?>
    <div class="alert alert-info">No hay cuentas corrientes registradas para este cliente.</div>
<?php else: ?>

    <!-- ──────────────────────────────────────────────────────────────────────
         Detalle de cada cuenta corriente (una por venta)
    ──────────────────────────────────────────────────────────────────────── -->
    <?php foreach ($cuentas as $cuenta): ?>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="fa-solid fa-file-invoice-dollar me-2"></i>
                <strong>Venta #<?= (int) $cuenta['id_venta'] ?></strong>
                &mdash; <?= htmlspecialchars($cuenta['fecha_venta'] ?? '—') ?>
            </span>
            <span class="badge <?= (float) $cuenta['saldo'] > 0 ? 'bg-danger' : 'bg-success' ?> fs-6">
                Saldo: $ <?= number_format((float) $cuenta['saldo'], 2, ',', '.') ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($cuenta['detalles'])): ?>
                <p class="text-muted p-3 mb-0">Sin movimientos de pago registrados.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th style="width: 130px;">Fecha</th>
                                <th class="text-end">Monto Pagado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuenta['detalles'] as $det): ?>
                            <tr>
                                <td><?= htmlspecialchars($det['fecha']) ?></td>
                                <td class="text-end text-success fw-semibold">
                                    $ <?= number_format((float) $det['monto'], 2, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

<?php endif; ?>

<!-- ──────────────────────────────────────────────────────────────────────────
     Formulario para registrar un nuevo pago
──────────────────────────────────────────────────────────────────────────── -->
<?php
// Solo mostrar el formulario si hay cuentas con saldo > 0
$cuentasConSaldo = array_filter($cuentas ?? [], fn($c) => (float) $c['saldo'] > 0);
?>
<?php if (!empty($cuentasConSaldo)): ?>
<div class="card mt-2">
    <div class="card-header bg-primary text-white">
        <i class="fa-solid fa-plus-circle me-2"></i>Registrar Pago
    </div>
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/cobranzas/registrar-pago">
            <input type="hidden" name="id_cliente" value="<?= (int) $cliente['id_cliente'] ?>">

            <div class="row g-3">
                <!-- Selector de cuenta corriente -->
                <div class="col-md-4">
                    <label for="id_cta_cte_cliente" class="form-label fw-semibold">
                        Cuenta Corriente (Venta)
                    </label>
                    <select name="id_cta_cte_cliente" id="id_cta_cte_cliente"
                            class="form-select" required>
                        <option value="">— Seleccioná una cuenta —</option>
                        <?php foreach ($cuentasConSaldo as $cuenta): ?>
                        <option value="<?= (int) $cuenta['id_cta_cte_cliente'] ?>">
                            Venta #<?= (int) $cuenta['id_venta'] ?>
                            (<?= htmlspecialchars($cuenta['fecha_venta'] ?? '') ?>)
                            — Saldo: $ <?= number_format((float) $cuenta['saldo'], 2, ',', '.') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Monto -->
                <div class="col-md-3">
                    <label for="monto" class="form-label fw-semibold">Monto a abonar</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="monto" id="monto"
                               class="form-control" min="0.01" step="0.01"
                               placeholder="0.00" required>
                    </div>
                </div>

                <!-- Fecha -->
                <div class="col-md-3">
                    <label for="fecha" class="form-label fw-semibold">Fecha</label>
                    <input type="date" name="fecha" id="fecha"
                           class="form-control"
                           value="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Botón -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Registrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Botón volver -->
<div class="mt-4">
    <a href="<?= URL_BASE ?>/cobranzas" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Volver a Cobranzas
    </a>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
