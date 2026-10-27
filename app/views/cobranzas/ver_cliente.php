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

<!-- Resumen de saldos -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <?php if ($saldoTotal > 0): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-0 h-100">
                <i class="fa-solid fa-circle-exclamation fa-lg"></i>
                <span>
                    Saldo adeudado (Cta Cte):
                    <strong>$ <?= number_format($saldoTotal, 0, ',', '.') ?></strong>
                </span>
            </div>
        <?php else: ?>
            <div class="alert alert-success d-flex align-items-center gap-2 mb-0 h-100">
                <i class="fa-solid fa-circle-check fa-lg"></i>
                <span>Sin deuda pendiente en cuenta corriente.</span>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <div class="alert <?= $saldoFavor > 0 ? 'alert-info' : 'alert-secondary' ?> d-flex align-items-center gap-2 mb-0 h-100">
            <i class="fa-solid fa-wallet fa-lg"></i>
            <span>
                Saldo a favor disponible:
                <strong class="fs-6">$ <?= number_format($saldoFavor, 0, ',', '.') ?></strong>
            </span>
        </div>
    </div>
</div>

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
                <a href="<?= URL_BASE ?>/ventas/show/<?= (int) $cuenta['id_venta'] ?>" target="_blank" rel="noopener"><strong>Venta #<?= (int) $cuenta['id_venta'] ?></strong></a>
                &mdash; <?= htmlspecialchars($cuenta['fecha_venta'] ?? '—') ?>
            </span>
            <span class="badge <?= (float) $cuenta['saldo'] > 0 ? 'bg-danger' : 'bg-success' ?> fs-6">
                Saldo: $ <?= number_format((float) $cuenta['saldo'], 0, ',', '.') ?>
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
                                <th>Concepto</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cuenta['detalles'] as $det): ?>
                            <?php $esDeuda = (float) $det['monto'] < 0; ?>
                            <tr>
                                <td><?= htmlspecialchars($det['fecha']) ?></td>
                                <td><?= htmlspecialchars($det['concepto'] ?? ($esDeuda ? 'Deuda' : 'Pago')) ?></td>
                                <td class="text-end fw-semibold <?= $esDeuda ? 'text-danger' : 'text-success' ?>">
                                    $ <?= number_format((float) $det['monto'], 0, ',', '.') ?>
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
                            — Saldo: $ <?= number_format((float) $cuenta['saldo'], 0, ',', '.') ?>
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

<!-- ──────────────────────────────────────────────────────────────────────────
     Saldo a favor: Formulario de Pago a Cuenta e Historial de Movimientos
──────────────────────────────────────────────────────────────────────────── -->
<div class="row g-4 mt-2">
    <!-- Formulario para registrar pago a cuenta (SIEMPRE visible) -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="fa-solid fa-hand-holding-dollar me-2"></i>Registrar Pago a Cuenta
            </div>
            <div class="card-body">
                <form method="POST" action="<?= URL_BASE ?>/cobranzas/registrar-pago-a-cuenta">
                    <input type="hidden" name="id_cliente" value="<?= (int) $cliente['id_cliente'] ?>">

                    <div class="mb-3">
                        <label for="pago_cuenta_monto" class="form-label fw-semibold">Monto</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto" id="pago_cuenta_monto"
                                   class="form-control" min="0.01" step="0.01"
                                   placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="pago_cuenta_medio" class="form-label fw-semibold">Medio de Ingreso</label>
                        <select name="id_medio_pago" id="pago_cuenta_medio" class="form-select" required>
                            <option value="">— Seleccioná un medio —</option>
                            <?php foreach ($mediosIngreso as $mp): ?>
                            <option value="<?= (int) $mp['id_medio_pago'] ?>">
                                <?= htmlspecialchars($mp['medio_pago']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="pago_cuenta_fecha" class="form-label fw-semibold">Fecha</label>
                        <input type="date" name="fecha" id="pago_cuenta_fecha"
                               class="form-control"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Ingresar a Saldo a Favor
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Movimientos de saldo a favor -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa-solid fa-clock-rotate-left me-2"></i>Historial de Saldo a Favor
                </span>
                <span class="badge <?= $saldoFavor > 0 ? 'bg-primary' : 'bg-secondary' ?>">
                    Disponible: $ <?= number_format($saldoFavor, 0, ',', '.') ?>
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($movimientosSaldo)): ?>
                    <p class="text-muted p-3 mb-0">Sin movimientos de saldo a favor registrados.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th style="width: 110px;">Fecha</th>
                                    <th>Tipo</th>
                                    <th>Detalle</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientosSaldo as $mov): ?>
                                <?php $esIngreso = ($mov['tipo'] === 'Ingreso'); ?>
                                <tr>
                                    <td><?= htmlspecialchars($mov['fecha']) ?></td>
                                    <td>
                                        <span class="badge <?= $esIngreso ? 'bg-success' : 'bg-primary' ?>">
                                            <?= htmlspecialchars($mov['tipo']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($esIngreso): ?>
                                            Ingreso vía <?= htmlspecialchars($mov['medio_pago_nombre'] ?? '—') ?>
                                        <?php else: ?>
                                            Uso en <?php if (!empty($mov['id_venta'])): ?><a href="<?= URL_BASE ?>/ventas/show/<?= (int) $mov['id_venta'] ?>" target="_blank" rel="noopener">Venta #<?= (int) $mov['id_venta'] ?></a><?php else: ?>venta<?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-semibold <?= $esIngreso ? 'text-success' : 'text-primary' ?>">
                                        <?= $esIngreso ? '+' : '-' ?> $ <?= number_format((float) $mov['monto'], 0, ',', '.') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Botón volver -->
<div class="mt-4">
    <a href="<?= URL_BASE ?>/cobranzas" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Volver a Cobranzas
    </a>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
