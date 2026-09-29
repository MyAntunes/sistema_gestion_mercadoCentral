<?php
/**
 * Router para PHP built-in server.
 * Uso: php -S localhost:3001 router.php
 *
 * Simula el comportamiento del .htaccess:
 *  - Si el archivo/directorio existe en public/ → lo sirve directamente (assets).
 *  - Todo lo demás → pasa por index.php con ?url=...
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Servir assets estáticos:
//  - /public/css/app.css  → __DIR__/public/css/app.css  (URI incluye /public/)
//  - cualquier archivo existente en la raíz del proyecto (fallback)
$fileFromRoot   = __DIR__ . $uri;
$fileFromPublic = __DIR__ . '/public' . $uri; // por si la URL NO trae /public/

if ($uri !== '/' && file_exists($fileFromRoot) && !is_dir($fileFromRoot)) {
    return false; // el servidor built-in sirve el archivo directamente
}
if ($uri !== '/' && file_exists($fileFromPublic) && !is_dir($fileFromPublic)) {
    return false;
}

// Extraer la URL sin la query string y pasarla como ?url=
$path = ltrim($uri, '/');
$_GET['url'] = $path;

// Ejecutar el Front Controller
require __DIR__ . '/index.php';
