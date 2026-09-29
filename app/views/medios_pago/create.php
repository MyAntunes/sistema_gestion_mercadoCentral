<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-credit-card me-2"></i>Nuevo Medio de Pago</h1>
</div>

<div class="card shadow-sm" style="max-width: 480px;">
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/medios-pago/store" novalidate>

            <div class="mb-4">
                <label for="medio_pago" class="form-label fw-semibold">
                    Medio de Pago <span class="text-danger">*</span>
                </label>
                <select id="medio_pago" name="medio_pago" class="form-select" required>
                    <option value="">— Seleccioná un medio de pago —</option>
                    <?php foreach ($enumValues as $valor): ?>
                        <option value="<?= htmlspecialchars($valor) ?>"
                            <?= ($_POST['medio_pago'] ?? '') === $valor ? 'selected' : '' ?>>
                            <?= htmlspecialchars($valor) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
                <a href="<?= URL_BASE ?>/medios-pago" class="btn btn-secondary">
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
