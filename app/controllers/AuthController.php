<?php
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Controlador de Autenticación
 */
class AuthController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Muestra el formulario de login.
     * Si ya hay sesión activa, redirige al inicio.
     */
    public function loginForm(): void
    {
        if (!empty($_SESSION['id_usuario'])) {
            redirect('/');
            return;
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesa el login (solo POST).
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/login');
            return;
        }

        $usuarioInput = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($usuarioInput) || empty($password)) {
            setFlash('danger', 'Debe ingresar su usuario o email y su contraseña.');
            redirect('/login');
            return;
        }

        $usuario = $this->usuarioModel->findByUsernameOrEmail($usuarioInput);

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            setFlash('danger', 'Usuario o contraseña incorrectos.');
            redirect('/login');
            return;
        }

        if ($usuario['estado'] !== 'Activo') {
            setFlash('danger', 'El usuario se encuentra inactivo. Contacte al administrador.');
            redirect('/login');
            return;
        }

        // Sesión válida
        $_SESSION['id_usuario'] = (int)$usuario['id_usuario'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
        $_SESSION['rol'] = $usuario['rol'];

        redirect('/');
    }

    /**
     * Destruye la sesión y redirige al login.
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: ' . URL_BASE . '/login');
        exit();
    }
}
