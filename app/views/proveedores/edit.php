<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-truck me-2"></i>Editar Proveedor</h1>
</div>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/proveedores/update/<?= (int) $proveedor['id_proveedor'] ?>" novalidate>

            <div class="mb-3">
                <label for="razon_social" class="form-label fw-semibold">
                    Razón Social <span class="text-danger">*</span>
                </label>
                <input type="text" id="razon_social" name="razon_social"
                       class="form-control"
                       value="<?= htmlspecialchars($proveedor['razon_social']) ?>"
                       required maxlength="100">
            </div>

            <div class="mb-3">
                <label for="direccion" class="form-label fw-semibold">Dirección</label>
                <input type="text" id="direccion" name="direccion"
                       class="form-control"
                       value="<?= htmlspecialchars($proveedor['direccion'] ?? '') ?>"
                       maxlength="100">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="telefono_1" class="form-label fw-semibold">
                        Teléfono 1 <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="telefono_1" name="telefono_1"
                           class="form-control"
                           value="<?= htmlspecialchars($proveedor['telefono_1']) ?>"
                           required maxlength="20">
                </div>
                <div class="col-md-6">
                    <label for="telefono_2" class="form-label fw-semibold">
                        Teléfono 2 <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="telefono_2" name="telefono_2"
                           class="form-control"
                           value="<?= htmlspecialchars($proveedor['telefono_2']) ?>"
                           required maxlength="20">
                </div>
            </div>

            <div class="mb-4">
                <label for="cuit" class="form-label fw-semibold">CUIT</label>
                <input type="text" id="cuit" name="cuit"
                       class="form-control"
                       value="<?= htmlspecialchars($proveedor['cuit'] ?? '') ?>"
                       maxlength="20">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
                <a href="<?= URL_BASE ?>/proveedores" class="btn btn-secondary">
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
