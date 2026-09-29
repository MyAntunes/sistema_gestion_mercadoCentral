<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>

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
<body>

<?php
// ── Helper: detectar si un path está activo ─────────────────────────────────
// Compara el inicio del REQUEST_URI contra el path dado (relativo a URL_BASE).
function navIsActive(string $path): bool {
    $uri  = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $base = rtrim(URL_BASE, '/');
    $full = $base . '/' . ltrim($path, '/');
    // Raíz (dashboard): coincidencia exacta
    if ($path === '' || $path === '/') {
        return ($uri === $base || $uri === $base . '/');
    }
    return str_starts_with($uri, $full);
}
?>

<?php if (!empty($_SESSION['id_usuario'])): ?>
<!-- ══════════════════════════════════════════════════════════════════════════
     NAVBAR
════════════════════════════════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?= URL_BASE ?>/">
            <i class="fa-solid fa-store me-1"></i><?= APP_NAME ?>
        </a>

        <!-- Toggle mobile -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navbarMain"
                aria-controls="navbarMain" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link<?= navIsActive('/') ? ' active' : '' ?>"
                       href="<?= URL_BASE ?>/">
                        <i class="fa-solid fa-gauge-high me-1"></i>Dashboard
                    </a>
                </li>

                <!-- Dropdown Maestros -->
                <?php
                $maestrosActive = navIsActive('clientes') || navIsActive('proveedores')
                               || navIsActive('productos') || navIsActive('medios-pago');
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= $maestrosActive ? ' active' : '' ?>"
                       href="#"
                       id="ddMaestros" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-list me-1"></i>Maestros
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="ddMaestros">
                        <li>
                            <a class="dropdown-item<?= navIsActive('clientes') ? ' active' : '' ?>"
                               href="<?= URL_BASE ?>/clientes">
                                <i class="fa-solid fa-users me-2"></i>Clientes
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item<?= navIsActive('proveedores') ? ' active' : '' ?>"
                               href="<?= URL_BASE ?>/proveedores">
                                <i class="fa-solid fa-truck me-2"></i>Proveedores
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item<?= navIsActive('productos') ? ' active' : '' ?>"
                               href="<?= URL_BASE ?>/productos">
                                <i class="fa-solid fa-box me-2"></i>Productos
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item<?= navIsActive('medios-pago') ? ' active' : '' ?>"
                               href="<?= URL_BASE ?>/medios-pago">
                                <i class="fa-solid fa-credit-card me-2"></i>Medios de Pago
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Ventas -->
                <li class="nav-item">
                    <a class="nav-link<?= navIsActive('ventas') ? ' active' : '' ?>"
                       href="<?= URL_BASE ?>/ventas">
                        <i class="fa-solid fa-shopping-cart me-1"></i>Ventas
                    </a>
                </li>

                <!-- Cobranzas -->
                <li class="nav-item">
                    <a class="nav-link<?= navIsActive('cobranzas') ? ' active' : '' ?>"
                       href="<?= URL_BASE ?>/cobranzas">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i>Cobranzas
                    </a>
                </li>

                <!-- Cheques -->
                <li class="nav-item">
                    <a class="nav-link<?= navIsActive('cheques') ? ' active' : '' ?>"
                       href="<?= URL_BASE ?>/cheques">
                        <i class="fa-solid fa-money-check me-1"></i>Cheques
                    </a>
                </li>

                <!-- Dropdown Admin (solo visible si es admin) -->
                <?php if (!empty($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= navIsActive('usuarios') ? ' active' : '' ?>"
                       href="#"
                       id="ddAdmin" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-gear me-1"></i>Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="ddAdmin">
                        <li>
                            <a class="dropdown-item<?= navIsActive('usuarios') ? ' active' : '' ?>"
                               href="<?= URL_BASE ?>/usuarios">
                                <i class="fa-solid fa-user-gear me-2"></i>Usuarios
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

            </ul>

            <!-- Usuario logueado + Cerrar sesión -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <li class="nav-item me-2">
                    <span class="badge bg-secondary fs-6">
                        <i class="fa-solid fa-user me-1"></i>
                        <?= htmlspecialchars($_SESSION['nombre_usuario'] ?? '') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm" href="<?= URL_BASE ?>/logout">
                        <i class="fa-solid fa-sign-out-alt me-1"></i>Salir
                    </a>
                </li>
            </ul>
        </div><!-- /.collapse -->
    </div><!-- /.container-fluid -->
</nav>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     MENSAJES FLASH
════════════════════════════════════════════════════════════════════════════ -->
<?php if (!empty($_SESSION['flash_message'])): ?>
    <?php
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        $alertType = htmlspecialchars($flash['type'] ?? 'info');
        $alertText = htmlspecialchars($flash['text'] ?? '');
    ?>
    <div class="container-fluid mt-2 px-4">
        <div class="alert alert-<?= $alertType ?> alert-dismissible fade show flash-alert" role="alert">
            <?= $alertText ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    </div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════════════════
     CONTENIDO PRINCIPAL
════════════════════════════════════════════════════════════════════════════ -->
<div class="container-fluid px-4 py-4">
