<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Cliente</h1>
</div>

<div class="card shadow-sm" style="max-width: 720px;">
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/clientes/store" novalidate>

            <div class="mb-3">
                <label for="nombre_apellido" class="form-label fw-semibold">
                    Nombre / Apellido <span class="text-danger">*</span>
                </label>
                <input type="text" id="nombre_apellido" name="nombre_apellido"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['nombre_apellido'] ?? '') ?>"
                       required maxlength="50">
            </div>

            <div class="mb-3">
                <label for="domicilio" class="form-label fw-semibold">Domicilio</label>
                <input type="text" id="domicilio" name="domicilio"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['domicilio'] ?? '') ?>"
                       maxlength="50">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label for="localidad" class="form-label fw-semibold">Localidad</label>
                    <input type="text" id="localidad" name="localidad"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['localidad'] ?? '') ?>"
                           maxlength="50">
                </div>
                <div class="col-md-4">
                    <label for="codigo_postal" class="form-label fw-semibold">Código Postal</label>
                    <input type="number" id="codigo_postal" name="codigo_postal"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['codigo_postal'] ?? '') ?>">
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="telefono_1" class="form-label fw-semibold">
                        Teléfono 1 <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="telefono_1" name="telefono_1"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['telefono_1'] ?? '') ?>"
                           required maxlength="20">
                </div>
                <div class="col-md-6">
                    <label for="telefono_2" class="form-label fw-semibold">
                        Teléfono 2 <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="telefono_2" name="telefono_2"
                           class="form-control"
                           value="<?= htmlspecialchars($_POST['telefono_2'] ?? '') ?>"
                           required maxlength="20">
                </div>
            </div>

            <div class="mb-4">
                <label for="cuil" class="form-label fw-semibold">CUIL</label>
                <input type="text" id="cuil" name="cuil"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['cuil'] ?? '') ?>"
                       maxlength="20">
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
                <a href="<?= URL_BASE ?>/clientes" class="btn btn-secondary">
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
