<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-key me-2"></i>Cambiar Contraseña</h1>
</div>

<div class="card shadow-sm mb-3" style="max-width: 500px;">
    <div class="card-header bg-light">
        <strong>Usuario:</strong> <?= htmlspecialchars($usuario['nombre_usuario']) ?>
        &nbsp;|&nbsp;
        <strong>Email:</strong> <?= htmlspecialchars($usuario['email']) ?>
        &nbsp;|&nbsp;
        <span class="badge <?= $usuario['rol'] === 'admin' ? 'bg-dark' : 'bg-secondary' ?>">
            <?= htmlspecialchars(ucfirst($usuario['rol'])) ?>
        </span>
    </div>
    <div class="card-body">
        <form method="POST"
              action="<?= URL_BASE ?>/usuarios/update-password/<?= (int) $usuario['id_usuario'] ?>"
              novalidate
              id="formCambiarPassword">

            <div class="mb-3">
                <label for="nueva_password" class="form-label fw-semibold">
                    Nueva Contraseña <span class="text-danger">*</span>
                </label>
                <input type="password" id="nueva_password" name="nueva_password"
                       class="form-control"
                       required minlength="6">
                <div class="form-text">Mínimo 6 caracteres.</div>
            </div>

            <div class="mb-4">
                <label for="confirmar_password" class="form-label fw-semibold">
                    Confirmar Contraseña <span class="text-danger">*</span>
                </label>
                <input type="password" id="confirmar_password" name="confirmar_password"
                       class="form-control"
                       required minlength="6">
                <div id="passError" class="text-danger small d-none">Las contraseñas no coinciden.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Actualizar Contraseña
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
    const form    = document.getElementById('formCambiarPassword');
    const pass    = document.getElementById('nueva_password');
    const confirm = document.getElementById('confirmar_password');
    const errMsg  = document.getElementById('passError');

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
