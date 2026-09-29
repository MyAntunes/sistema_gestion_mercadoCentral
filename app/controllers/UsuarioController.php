<?php
require_once __DIR__ . '/../models/Usuario.php';

/**
 * Controller Usuario — solo accesible por admins
 */
class UsuarioController
{
    private Usuario $model;

    public function __construct()
    {
        $this->model = new Usuario();
    }

    /**
     * Verifica que el usuario esté autenticado y sea admin.
     */
    private function requireAdmin(): void
    {
        requireAuth();
        if (($_SESSION['rol'] ?? '') !== 'admin') {
            setFlash('danger', 'Acceso restringido. Solo administradores.');
            redirect('/');
        }
    }

    /**
     * GET /usuarios — listado de usuarios
     */
    public function index(): void
    {
        $this->requireAdmin();

        $usuarios  = $this->model->getAll();
        $pageTitle = 'Usuarios';
        include __DIR__ . '/../views/usuarios/index.php';
    }

    /**
     * GET /usuarios/create — formulario de alta
     */
    public function create(): void
    {
        $this->requireAdmin();

        $pageTitle = 'Nuevo Usuario';
        include __DIR__ . '/../views/usuarios/create.php';
    }

    /**
     * POST /usuarios/store — procesa el alta
     */
    public function store(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios');
            return;
        }

        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $email          = trim($_POST['email']          ?? '');
        $password       = $_POST['password']            ?? '';
        $rol            = trim($_POST['rol']            ?? '');

        // Validar requeridos
        if ($nombre_usuario === '' || $email === '' || $password === '' || $rol === '') {
            setFlash('danger', 'Todos los campos marcados con * son requeridos.');
            redirect('usuarios/create');
            return;
        }

        // Validar longitud mínima de password
        if (strlen($password) < 6) {
            setFlash('danger', 'La contraseña debe tener al menos 6 caracteres.');
            redirect('usuarios/create');
            return;
        }

        // Validar unicidad de nombre_usuario
        if ($this->model->findByUsernameOrEmail($nombre_usuario)) {
            setFlash('danger', 'El nombre de usuario ya está en uso.');
            redirect('usuarios/create');
            return;
        }

        // Validar unicidad de email
        if ($this->model->findByUsernameOrEmail($email)) {
            setFlash('danger', 'El email ya está en uso.');
            redirect('usuarios/create');
            return;
        }

        $data = [
            'nombre_usuario' => $nombre_usuario,
            'email'          => $email,
            'password'       => $password,
            'rol'            => $rol,
            'estado'         => $_POST['estado'] ?? 'Activo',
        ];

        if ($this->model->create($data)) {
            setFlash('success', 'Usuario creado correctamente.');
            redirect('usuarios');
        } else {
            setFlash('danger', 'Error al crear el usuario. Intentá de nuevo.');
            redirect('usuarios/create');
        }
    }

    /**
     * GET /usuarios/edit/{id} — formulario de edición
     */
    public function edit(int $id): void
    {
        $this->requireAdmin();

        $usuario = $this->model->getById($id);
        if (!$usuario) {
            setFlash('warning', 'Usuario no encontrado.');
            redirect('usuarios');
            return;
        }

        $pageTitle = 'Editar Usuario';
        include __DIR__ . '/../views/usuarios/edit.php';
    }

    /**
     * POST /usuarios/update/{id} — procesa la edición (sin password)
     */
    public function update(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios');
            return;
        }

        $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
        $email          = trim($_POST['email']          ?? '');
        $rol            = trim($_POST['rol']            ?? '');

        if ($nombre_usuario === '' || $email === '' || $rol === '') {
            setFlash('danger', 'Todos los campos marcados con * son requeridos.');
            redirect('usuarios/edit/' . $id);
            return;
        }

        // Validar que nombre_usuario no esté en uso por otro usuario
        $existente = $this->model->findByUsernameOrEmail($nombre_usuario);
        if ($existente && (int)$existente['id_usuario'] !== $id) {
            setFlash('danger', 'El nombre de usuario ya está en uso por otro usuario.');
            redirect('usuarios/edit/' . $id);
            return;
        }

        // Validar que email no esté en uso por otro usuario
        $existente = $this->model->findByUsernameOrEmail($email);
        if ($existente && (int)$existente['id_usuario'] !== $id) {
            setFlash('danger', 'El email ya está en uso por otro usuario.');
            redirect('usuarios/edit/' . $id);
            return;
        }

        $data = [
            'nombre_usuario' => $nombre_usuario,
            'email'          => $email,
            'rol'            => $rol,
            'estado'         => $_POST['estado'] ?? 'Activo',
        ];

        if ($this->model->update($id, $data)) {
            setFlash('success', 'Usuario actualizado correctamente.');
            redirect('usuarios');
        } else {
            setFlash('danger', 'Error al actualizar el usuario. Intentá de nuevo.');
            redirect('usuarios/edit/' . $id);
        }
    }

    /**
     * GET /usuarios/change-password/{id} — formulario de cambio de contraseña
     */
    public function changePassword(int $id): void
    {
        $this->requireAdmin();

        $usuario = $this->model->getById($id);
        if (!$usuario) {
            setFlash('warning', 'Usuario no encontrado.');
            redirect('usuarios');
            return;
        }

        $pageTitle = 'Cambiar Contraseña';
        include __DIR__ . '/../views/usuarios/change_password.php';
    }

    /**
     * POST /usuarios/update-password/{id} — procesa el cambio de contraseña
     */
    public function updatePassword(int $id): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('usuarios');
            return;
        }

        $usuario = $this->model->getById($id);
        if (!$usuario) {
            setFlash('warning', 'Usuario no encontrado.');
            redirect('usuarios');
            return;
        }

        $nueva_password    = $_POST['nueva_password']    ?? '';
        $confirmar_password = $_POST['confirmar_password'] ?? '';

        if (strlen($nueva_password) < 6) {
            setFlash('danger', 'La nueva contraseña debe tener al menos 6 caracteres.');
            redirect('usuarios/change-password/' . $id);
            return;
        }

        if ($nueva_password !== $confirmar_password) {
            setFlash('danger', 'Las contraseñas no coinciden.');
            redirect('usuarios/change-password/' . $id);
            return;
        }

        if ($this->model->updatePassword($id, $nueva_password)) {
            setFlash('success', 'Contraseña actualizada correctamente.');
            redirect('usuarios');
        } else {
            setFlash('danger', 'Error al actualizar la contraseña. Intentá de nuevo.');
            redirect('usuarios/change-password/' . $id);
        }
    }

    /**
     * GET|POST /usuarios/toggle-estado/{id} — alterna Activo/Inactivo
     * No puede desactivar al propio usuario logueado.
     */
    public function toggleEstado(int $id): void
    {
        $this->requireAdmin();

        if ((int)$_SESSION['id_usuario'] === $id) {
            setFlash('warning', 'No podés cambiar el estado de tu propio usuario.');
            redirect('usuarios');
            return;
        }

        $usuario = $this->model->getById($id);
        if (!$usuario) {
            setFlash('warning', 'Usuario no encontrado.');
            redirect('usuarios');
            return;
        }

        if ($this->model->toggleEstado($id)) {
            $nuevoEstado = ($usuario['estado'] === 'Activo') ? 'Inactivo' : 'Activo';
            setFlash('success', 'Estado cambiado a ' . $nuevoEstado . '.');
        } else {
            setFlash('danger', 'Error al cambiar el estado.');
        }

        redirect('usuarios');
    }

    /**
     * GET|POST /usuarios/delete/{id} — elimina el usuario
     * No puede eliminar al propio usuario logueado.
     */
    public function delete(int $id): void
    {
        $this->requireAdmin();

        if ((int)$_SESSION['id_usuario'] === $id) {
            setFlash('warning', 'No podés eliminar tu propio usuario.');
            redirect('usuarios');
            return;
        }

        if ($this->model->delete($id)) {
            setFlash('success', 'Usuario eliminado correctamente.');
        } else {
            setFlash('danger', 'Error al eliminar el usuario.');
        }

        redirect('usuarios');
    }
}
