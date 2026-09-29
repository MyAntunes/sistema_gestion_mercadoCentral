<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-box me-2"></i>Editar Producto</h1>
</div>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/productos/update/<?= (int) $producto['id_producto'] ?>" novalidate>

            <div class="mb-3">
                <label for="nombre" class="form-label fw-semibold">
                    Nombre <span class="text-danger">*</span>
                </label>
                <input type="text" id="nombre" name="nombre"
                       class="form-control"
                       value="<?= htmlspecialchars($producto['nombre']) ?>"
                       required maxlength="100">
            </div>

            <div class="mb-3">
                <label for="especie" class="form-label fw-semibold">Especie</label>
                <input type="text" id="especie" name="especie"
                       class="form-control"
                       value="<?= htmlspecialchars($producto['especie'] ?? '') ?>"
                       maxlength="100">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="precio_costo" class="form-label fw-semibold">
                        Precio Costo <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="precio_costo" name="precio_costo"
                               class="form-control"
                               value="<?= htmlspecialchars($producto['precio_costo']) ?>"
                               step="0.01" min="0" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="precio_venta" class="form-label fw-semibold">
                        Precio Venta <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="precio_venta" name="precio_venta"
                               class="form-control"
                               value="<?= htmlspecialchars($producto['precio_venta']) ?>"
                               step="0.01" min="0" required>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="id_proveedor" class="form-label fw-semibold">
                    Proveedor <span class="text-danger">*</span>
                </label>
                <select id="id_proveedor" name="id_proveedor" class="form-select" required>
                    <option value="">— Seleccioná un proveedor —</option>
                    <?php foreach ($proveedores as $prov): ?>
                        <option value="<?= (int) $prov['id_proveedor'] ?>"
                            <?= (int) $producto['id_proveedor'] === (int) $prov['id_proveedor'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($prov['razon_social']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
                <a href="<?= URL_BASE ?>/productos" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </a>
            </div>

            <p class="text-muted small mt-3 mb-0">
                <span class="text-danger">*</span> Campos requeridos.
            </p>

        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
