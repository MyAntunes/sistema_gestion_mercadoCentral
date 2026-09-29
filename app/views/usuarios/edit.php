<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><i class="fa-solid fa-user-pen me-2"></i>Editar Usuario</h1>
</div>

<div class="card shadow-sm" style="max-width: 600px;">
    <div class="card-body">
        <form method="POST" action="<?= URL_BASE ?>/usuarios/update/<?= (int) $usuario['id_usuario'] ?>" novalidate>

            <div class="mb-3">
                <label for="nombre_usuario" class="form-label fw-semibold">
                    Usuario <span class="text-danger">*</span>
                </label>
                <input type="text" id="nombre_usuario" name="nombre_usuario"
                       class="form-control"
                       value="<?= htmlspecialchars($usuario['nombre_usuario']) ?>"
                       required maxlength="50">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email" id="email" name="email"
                       class="form-control"
                       value="<?= htmlspecialchars($usuario['email']) ?>"
                       required maxlength="100">
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="rol" class="form-label fw-semibold">
                        Rol <span class="text-danger">*</span>
                    </label>
                    <select id="rol" name="rol" class="form-select" required>
                        <option value="admin"   <?= $usuario['rol'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                        <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="estado" class="form-label fw-semibold">Estado</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="Activo"   <?= $usuario['estado'] === 'Activo'   ? 'selected' : '' ?>>Activo</option>
                        <option value="Inactivo" <?= $usuario['estado'] === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mb-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar Cambios
                </button>
                <a href="<?= URL_BASE ?>/usuarios" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </a>
            </div>

            <p class="text-muted small mb-0">
                <span class="text-danger">*</span> Campos requeridos.
                La contraseña no se modifica aquí —
                <a href="<?= URL_BASE ?>/usuarios/change-password/<?= (int) $usuario['id_usuario'] ?>">
                    Cambiar Contraseña
                </a>
            </p>

        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
