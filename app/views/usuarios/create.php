<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-user-plus me-2"></i>Nuevo Usuario</h1>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/usuarios/store" novalidate id="formCrearUsuario">

            <div class="mb-3">
                <label for="nombre_usuario" class="form-label fw-semibold">
                    Usuario <span class="text-danger">*</span>
                </label>
                <input type="text" id="nombre_usuario" name="nombre_usuario"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['nombre_usuario'] ?? '') ?>"
                       required maxlength="50">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email" id="email" name="email"
                       class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       required maxlength="100">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">
                    Contraseña <span class="text-danger">*</span>
                </label>
                <input type="password" id="password" name="password"
                       class="form-control"
                       required minlength="6">
                <div class="form-text">Mínimo 6 caracteres.</div>
            </div>

            <div class="mb-3">
                <label for="confirmar_password" class="form-label fw-semibold">
                    Confirmar Contraseña <span class="text-danger">*</span>
                </label>
                <input type="password" id="confirmar_password" name="confirmar_password"
                       class="form-control"
                       required minlength="6">
                <div id="passError" class="text-danger small d-none">Las contraseñas no coinciden.</div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="rol" class="form-label fw-semibold">
                        Rol <span class="text-danger">*</span>
                    </label>
                    <select id="rol" name="rol" class="form-select" required>
                        <option value="">— Seleccioná un rol —</option>
                        <option value="admin"   <?= ($_POST['rol'] ?? '') === 'admin'    ? 'selected' : '' ?>>Admin</option>
                        <option value="vendedor" <?= ($_POST['rol'] ?? '') === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="estado" class="form-label fw-semibold">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="Activo"   <?= ($_POST['estado'] ?? 'Activo') === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= ($_POST['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                </button>
                <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </a>
            </div>

            <p class="text-muted small mt-3 mb-0">
                <span class="text-danger">*</span> Campos requeridos.
            </p>

        </form>
    </div>
</div>

<script>
(function () {
    const form     = document.getElementById('formCrearUsuario');
    const pass     = document.getElementById('password');
    const confirm  = document.getElementById('confirmar_password');
    const errMsg   = document.getElementById('passError');

    function checkPasswords() {
        if (confirm.value !== '' && pass.value !== confirm.value) {
            errMsg.classList.remove('d-none');
            confirm.classList.add('is-invalid');
        } else {
            errMsg.classList.add('d-none');
            confirm.classList.remove('is-invalid');
        }
    }

    pass.addEventListener('input', checkPasswords);
    confirm.addEventListener('input', checkPasswords);

    form.addEventListener('submit', function (e) {
        if (pass.value !== confirm.value) {
            e.preventDefault();
            errMsg.classList.remove('d-none');
            confirm.classList.add('is-invalid');
            confirm.focus();
        }
    });
})();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
