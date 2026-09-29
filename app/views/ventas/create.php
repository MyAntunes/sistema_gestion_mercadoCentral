<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa-solid fa-cart-plus me-2 text-primary"></i>Registrar Nueva Venta
        </h1>
        <p class="text-muted small mb-0">Carga de productos y selección de formas de pago</p>
    </div>
    <div>
        <a href="<?= URL_BASE ?>/ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver al listado
        </a>
    </div>
</div>

<form id="formVenta" method="POST" action="<?= URL_BASE ?>/ventas/store">
    
    <!-- SECCIÓN 1: Datos de la Venta -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fa-solid fa-info-circle me-1"></i>1. Datos de la Venta
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="fecha" class="form-label fw-semibold">Fecha <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-8">
                    <label for="id_cliente" class="form-label fw-semibold">Cliente <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_cliente" name="id_cliente" required>
                        <option value="">-- Seleccione un cliente --</option>
                        <?php foreach ($clientes as $cli): ?>
                            <option value="<?= $cli['id_cliente'] ?>">
                                <?= htmlspecialchars($cli['nombre_apellido']) ?> 
                                <?= !empty($cli['cuil']) ? ' (CUIL: ' . htmlspecialchars($cli['cuil']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: Productos -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fa-solid fa-box-open me-1"></i>2. Detalle de Productos
            </h6>
            <button type="button" class="btn btn-sm btn-success" id="btnAgregarProducto" onclick="addProductRow()">
                <i class="fa-solid fa-plus me-1"></i>Agregar Producto
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0" id="tablaProductos">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 250px;">Producto <span class="text-danger">*</span></th>
                            <th style="width: 150px;">Precio Unitario ($)</th>
                            <th style="width: 120px;">Cantidad <span class="text-danger">*</span></th>
                            <th style="width: 120px;">Descuento %</th>
                            <th style="width: 150px;" class="text-end">Subtotal ($)</th>
                            <th style="width: 70px;" class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyProductos">
                        <!-- Filas dinámicas generadas por JS -->
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end fs-6">TOTAL VENTA:</td>
                            <td class="text-end fs-5 text-primary" id="lblTotalVenta">$0,00</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: Medios de Pago -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fa-solid fa-money-bill-wave me-1"></i>3. Medios de Pago
            </h6>
            <button type="button" class="btn btn-sm btn-success" id="btnAgregarPago" onclick="addPagoRow()">
                <i class="fa-solid fa-plus me-1"></i>Agregar Medio de Pago
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="tablaPagos">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 200px;">Medio de Pago <span class="text-danger">*</span></th>
                            <th style="width: 180px;">Monto ($) <span class="text-danger">*</span></th>
                            <th>Detalles Adicionales (Cheques)</th>
                            <th style="width: 70px;" class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPagos">
                        <!-- Filas dinámicas generadas por JS -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td class="text-end fs-6">TOTAL PAGOS:</td>
                            <td class="fs-5" id="lblTotalPagos">$0,00</td>
                            <td colspan="2">
                                <span id="lblDiferenciaBadge" class="badge bg-secondary fs-6">
                                    Diferencia: $0,00
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Acciones Finales -->
    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="<?= URL_BASE ?>/ventas" class="btn btn-outline-secondary">
            <i class="fa-solid fa-xmark me-1"></i>Cancelar
        </a>
        <button type="submit" class="btn btn-primary px-4" id="btnSubmitVenta">
            <i class="fa-solid fa-check me-1"></i>Registrar Venta
        </button>
    </div>

</form>

<!-- Inyección de datos PHP para Javascript -->
<script>
    var ventaProductos = <?= json_encode($productos, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
    var ventaMediosPago = <?= json_encode($mediosPago, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
