<?php
// ── Front Controller ───────────────────────────────────────────────────────────
require_once __DIR__ . '/config/config.php';

// ── Helper: proteger rutas ─────────────────────────────────────────────────────
function requireAuth(): void
{
    if (empty($_SESSION['id_usuario'])) {
        header('Location: ' . URL_BASE . '/login');
        exit();
    }
}

// ── Helper: redirigir ──────────────────────────────────────────────────────────
function redirect(string $path): void
{
    header('Location: ' . URL_BASE . '/' . ltrim($path, '/'));
    exit();
}

// ── Helper: mensaje flash ──────────────────────────────────────────────────────
function setFlash(string $type, string $message): void
{
    $_SESSION['flash_message'] = ['type' => $type, 'text' => $message];
}

// ── Autoload de controllers ────────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $file = __DIR__ . '/app/controllers/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Parsear URL ────────────────────────────────────────────────────────────────
$url = trim($_GET['url'] ?? '', '/');
$url = filter_var($url, FILTER_SANITIZE_URL);
$parts = explode('/', $url);

$segment0 = strtolower($parts[0] ?? '');   // controller/ruta principal
$segment1 = strtolower($parts[1] ?? '');   // acción
$param    = $parts[2] ?? null;             // parámetro (id, etc.)

// ── Tabla de rutas ─────────────────────────────────────────────────────────────
// Rutas públicas (sin autenticación)
$publicRoutes = ['login', 'logout'];

// Mapa: segmento0 => [segmento1 => [controller, action]]
$routes = [
    // Auth
    'login'  => [
        ''       => $_SERVER['REQUEST_METHOD'] === 'POST' ? ['AuthController', 'login'] : ['AuthController', 'loginForm'],
        'login'  => $_SERVER['REQUEST_METHOD'] === 'POST' ? ['AuthController', 'login'] : ['AuthController', 'loginForm'],
        'store'  => ['AuthController', 'login'],
    ],
    'logout' => [
        ''       => ['AuthController', 'logout'],
    ],

    // Home / Dashboard
    '' => [
        '' => ['HomeController', 'index'],
    ],
    'home' => [
        ''              => ['HomeController', 'index'],
        'index'         => ['HomeController', 'index'],
        'graficos-data' => ['HomeController', 'graficosData'],
    ],

    // Clientes
    'clientes' => [
        ''       => ['ClienteController', 'index'],
        'index'  => ['ClienteController', 'index'],
        'create' => ['ClienteController', 'create'],
        'store'  => ['ClienteController', 'store'],
        'edit'   => ['ClienteController', 'edit'],     // + param id
        'update' => ['ClienteController', 'update'],   // + param id
        'delete' => ['ClienteController', 'delete'],   // + param id
    ],

    // Proveedores
    'proveedores' => [
        ''       => ['ProveedorController', 'index'],
        'index'  => ['ProveedorController', 'index'],
        'create' => ['ProveedorController', 'create'],
        'store'  => ['ProveedorController', 'store'],
        'edit'   => ['ProveedorController', 'edit'],
        'update' => ['ProveedorController', 'update'],
        'delete' => ['ProveedorController', 'delete'],
    ],

    // Productos
    'productos' => [
        ''       => ['ProductoController', 'index'],
        'index'  => ['ProductoController', 'index'],
        'create' => ['ProductoController', 'create'],
        'store'  => ['ProductoController', 'store'],
        'edit'   => ['ProductoController', 'edit'],
        'update' => ['ProductoController', 'update'],
        'delete' => ['ProductoController', 'delete'],
    ],

    // Medios de Pago
    'medios-pago' => [
        ''       => ['MedioPagoController', 'index'],
        'index'  => ['MedioPagoController', 'index'],
        'create' => ['MedioPagoController', 'create'],
        'store'  => ['MedioPagoController', 'store'],
        'edit'   => ['MedioPagoController', 'edit'],
        'update' => ['MedioPagoController', 'update'],
        'delete' => ['MedioPagoController', 'delete'],
    ],

    // Ventas
    'ventas' => [
        ''       => ['VentaController', 'index'],
        'index'  => ['VentaController', 'index'],
        'create' => ['VentaController', 'create'],
        'store'  => ['VentaController', 'store'],
        'show'   => ['VentaController', 'show'],       // + param id
    ],

    // Cobranzas
    'cobranzas' => [
        ''                => ['CobranzaController', 'index'],
        'index'           => ['CobranzaController', 'index'],
        'cliente'         => ['CobranzaController', 'verCliente'],    // + param id_cliente
        'registrar-pago'  => ['CobranzaController', 'registrarPago'], // POST
    ],

    // Cheques
    'cheques' => [
        ''              => ['ChequeController', 'index'],
        'index'         => ['ChequeController', 'index'],
        'update-estado' => ['ChequeController', 'updateEstado'], // + param id
    ],

    // Usuarios
    'usuarios' => [
        ''                => ['UsuarioController', 'index'],
        'index'           => ['UsuarioController', 'index'],
        'create'          => ['UsuarioController', 'create'],
        'store'           => ['UsuarioController', 'store'],
        'edit'            => ['UsuarioController', 'edit'],            // + param id
        'update'          => ['UsuarioController', 'update'],          // + param id
        'change-password' => ['UsuarioController', 'changePassword'],  // + param id
        'update-password' => ['UsuarioController', 'updatePassword'],  // + param id
        'toggle-estado'   => ['UsuarioController', 'toggleEstado'],    // + param id
        'delete'          => ['UsuarioController', 'delete'],          // + param id
    ],
];

// ── Resolver ruta ──────────────────────────────────────────────────────────────
$controllerClass  = null;
$actionMethod     = null;

if (isset($routes[$segment0])) {
    $moduleRoutes = $routes[$segment0];

    if (isset($moduleRoutes[$segment1])) {
        [$controllerClass, $actionMethod] = $moduleRoutes[$segment1];
    } elseif (isset($moduleRoutes[''])) {
        // Acción vacía: el segment1 puede ser un ID numérico
        [$controllerClass, $actionMethod] = $moduleRoutes[''];
        if ($segment1 !== '' && $param === null) {
            $param = $segment1;
        }
    }
}

// Ruta por defecto: si no hay sesión → login, si hay → home
if ($controllerClass === null) {
    if (empty($_SESSION['id_usuario'])) {
        $controllerClass = 'AuthController';
        $actionMethod    = 'loginForm';
    } else {
        $controllerClass = 'HomeController';
        $actionMethod    = 'index';
    }
}

// ── Proteger rutas privadas ────────────────────────────────────────────────────
$isPublic = in_array($segment0, ['login', '']) && $segment0 !== '';
// login y logout son públicos; todo lo demás requiere auth
if (!in_array($segment0, $publicRoutes) && $segment0 !== 'login') {
    if (empty($_SESSION['id_usuario'])) {
        header('Location: ' . URL_BASE . '/login');
        exit();
    }
}

// ── Cargar controller y ejecutar acción ───────────────────────────────────────
$controllerFile = __DIR__ . '/app/controllers/' . $controllerClass . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo '<h1>404 — Página no encontrada</h1>';
    exit();
}

require_once $controllerFile;

$controller = new $controllerClass();

if (!method_exists($controller, $actionMethod)) {
    http_response_code(404);
    echo '<h1>404 — Acción no encontrada</h1>';
    exit();
}

if ($param !== null) {
    $controller->{$actionMethod}($param);
} else {
    $controller->{$actionMethod}();
}
