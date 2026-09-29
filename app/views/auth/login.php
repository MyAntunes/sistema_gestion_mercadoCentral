<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — <?= APP_NAME ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          rel="stylesheet"
          crossorigin="anonymous">

    <!-- CSS propio -->
    <link href="<?= URL_BASE ?>/public/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="login-wrapper">
    <div class="login-card p-4">
        <!-- Logo / Encabezado -->
        <div class="text-center mb-4">
            <div class="mb-2">
                <i class="fa-solid fa-store text-primary" style="font-size: 2.5rem;"></i>
            </div>
            <h2 class="fw-bold mb-1"><?= APP_NAME ?></h2>
            <p class="text-muted small">Sistema de Gestión de Ventas</p>
        </div>

        <!-- Mensajes Flash -->
        <?php if (!empty($_SESSION['flash_message'])): ?>
            <?php
                $flash = $_SESSION['flash_message'];
                unset($_SESSION['flash_message']);
                $alertType = htmlspecialchars($flash['type'] ?? 'info');
                $alertText = htmlspecialchars($flash['text'] ?? '');
            ?>
            <div class="alert alert-<?= $alertType ?> alert-dismissible fade show mb-3" role="alert">
                <?= $alertText ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <!-- Card Formulario -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h5 class="card-title text-center mb-4 fw-semibold">Iniciar Sesión</h5>

                <form action="<?= URL_BASE ?>/login" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario o Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user text-secondary"></i></span>
                            <input type="text"
                                   class="form-control"
                                   id="usuario"
                                   name="usuario"
                                   placeholder="Ingrese usuario o email"
                                   required
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock text-secondary"></i></span>
                            <input type="password"
                                   class="form-control"
                                   id="password"
                                   name="password"
                                   placeholder="Ingrese su contraseña"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Ingresar
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer mínimo -->
        <div class="text-center text-muted small mt-4">
            &copy; <?= date('Y') ?> <?= APP_NAME ?>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmQnGlRXzFGfq6zsBPvpFVzJ4Zls"
        crossorigin="anonymous"></script>

</body>
</html>
