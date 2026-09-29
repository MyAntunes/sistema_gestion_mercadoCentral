<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa-solid fa-receipt me-2 text-primary"></i>Detalle de Venta #<?= $venta['id_venta'] ?>
        </h1>
        <p class="text-muted small mb-0">Información completa de la operación</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= URL_BASE ?>/ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver al listado
        </a>
        <a href="<?= URL_BASE ?>/ventas/create" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>Nueva Venta
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Información de Cabecera -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fa-solid fa-file-invoice me-1"></i>Datos de la Venta
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 140px;">Número de Venta:</th>
                        <td class="fw-bold">#<?= $venta['id_venta'] ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Fecha:</th>
                        <td>
                            <i class="fa-regular fa-calendar me-1 text-muted"></i>
                            <?= date('d/m/Y', strtotime($venta['fecha'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Registrado por:</th>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($venta['nombre_usuario']) ?>
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light py-3">
                <h6 class="m-0 fw-bold text-primary">
                    <i class="fa-solid fa-user me-1"></i>Datos del Cliente
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted" style="width: 140px;">Nombre / Razón:</th>
                        <td class="fw-bold"><?= htmlspecialchars($venta['nombre_apellido']) ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">CUIL / CUIT:</th>
                        <td><?= htmlspecialchars($venta['cuil'] ?: 'No registrado') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Teléfono:</th>
                        <td><?= htmlspecialchars($venta['telefono_1'] ?: 'No registrado') ?></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Domicilio:</th>
                        <td><?= htmlspecialchars($venta['domicilio'] ?: 'No registrado') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light py-3">
        <h6 class="m-0 fw-bold text-primary">
            <i class="fa-solid fa-boxes-stacked me-1"></i>Productos Facturados
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>Producto</th>
                        <th class="text-end" style="width: 150px;">Precio Unit.</th>
                        <th class="text-center" style="width: 120px;">Cantidad</th>
                        <th class="text-center" style="width: 120px;">Descuento</th>
                        <th class="text-end" style="width: 160px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalVentaCalc = 0.0;
                    $i = 1;
                    foreach ($detalles as $det): 
                        $pu = (float) $det['precio_unitario'];
                        $cant = (int) $det['cantidad'];
                        $desc = (float) ($det['descuento'] ?? 0);
                        $subtotal = $pu * $cant * (1 - ($desc / 100));
                        $totalVentaCalc += $subtotal;
                    ?>
                        <tr>
                            <td class="text-center text-muted"><?= $i++ ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($det['producto_nombre']) ?></div>
                                <?php if (!empty($det['producto_especie'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($det['producto_especie']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">$<?= number_format($pu, 2, ',', '.') ?></td>
                            <td class="text-center"><?= $cant ?></td>
                            <td class="text-center"><?= $desc > 0 ? $desc . '%' : '-' ?></td>
                            <td class="text-end fw-bold">$<?= number_format($subtotal, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="5" class="text-end fs-6">TOTAL PRODUCTOS:</td>
                        <td class="text-end text-primary fs-5">$<?= number_format($totalVentaCalc, 2, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Medios de Pago y Detalle Financiero -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light py-3">
        <h6 class="m-0 fw-bold text-primary">
            <i class="fa-solid fa-money-bill-wave me-1"></i>Medios de Pago Utilizados
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 200px;">Medio de Pago</th>
                        <th class="text-end" style="width: 160px;">Monto</th>
                        <th>Detalle / Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalPagosCalc = 0.0;
                    foreach ($mediosPago as $pago): 
                        $montoPago = (float) $pago['monto'];
                        $totalPagosCalc += $montoPago;
                    ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary fs-6">
                                    <?= htmlspecialchars($pago['medio_pago_nombre']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold fs-6">$<?= number_format($montoPago, 2, ',', '.') ?></td>
                            <td>
                                <?php if (!empty($pago['id_cheque'])): ?>
                                    <div class="p-2 border rounded bg-light">
                                        <div class="fw-bold text-dark">
                                            <i class="fa-solid fa-money-check me-1"></i>Cheque <?= htmlspecialchars($pago['tipo_cheque']) ?> #<?= htmlspecialchars($pago['numero_cheque']) ?> — <?= htmlspecialchars($pago['banco']) ?>
                                        </div>
                                        <div class="small text-muted mt-1">
                                            <span><strong>Emisión:</strong> <?= date('d/m/Y', strtotime($pago['fecha_emision'])) ?></span> |
                                            <span><strong>Pago:</strong> <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></span> |
                                            <span><strong>Estado:</strong> 
                                                <span class="badge <?= $pago['cheque_estado'] === 'Cobrado' ? 'bg-success' : ($pago['cheque_estado'] === 'Rechazado' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                    <?= htmlspecialchars($pago['cheque_estado']) ?>
                                                </span>
                                            </span>
                                        </div>
                                        <?php if (!empty($pago['cheque_observaciones'])): ?>
                                            <div class="small text-muted mt-1">
                                                <em>Obs: <?= htmlspecialchars($pago['cheque_observaciones']) ?></em>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="<?= URL_BASE ?>/cheques" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Ir a Gestión de Cheques
                                            </a>
                                        </div>
                                    </div>
                                <?php elseif ($pago['medio_pago_nombre'] === 'Cuenta Corriente'): ?>
                                    <div class="p-2 border rounded bg-warning-subtle text-dark">
                                        <div class="fw-bold">
                                            <i class="fa-solid fa-book-open me-1"></i>Imputado a Cuenta Corriente
                                        </div>
                                        <?php if ($ctaCte): ?>
                                            <div class="small mt-1">
                                                <span><strong>Saldo pendiente de este comprobante:</strong> $<?= number_format((float)$ctaCte['saldo'], 2, ',', '.') ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="<?= URL_BASE ?>/cobranzas" class="btn btn-sm btn-warning py-0 px-2 text-dark fw-semibold" style="font-size: 0.8rem;">
                                                <i class="fa-solid fa-hand-holding-dollar me-1"></i>Ir al Módulo de Cobranzas
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i>Cancelado al contado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td class="text-end fs-6">TOTAL PAGADO:</td>
                        <td class="text-end text-success fs-5">$<?= number_format($totalPagosCalc, 2, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
