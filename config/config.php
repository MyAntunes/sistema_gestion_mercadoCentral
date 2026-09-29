<?php
// ── URL base dinámica: funciona en raíz o en cualquier subcarpeta ──────────────
define('URL_BASE', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'));

// ── Base de datos local ──────────────────────────────────────────────────────────────
define('DB_HOST',   '127.0.0.1:3306');
define('DB_NAME',   'lo_de_carlitos');
define('DB_USER',   'root');
define('DB_PASS',   'cablevision');
define('DB_CHARSET','utf8mb4');


// ── Aplicación ─────────────────────────────────────────────────────────────────
define('APP_NAME',    'Lo de Carlitos');
define('APP_VERSION', '1.0.0');

// ── Sesión ─────────────────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}