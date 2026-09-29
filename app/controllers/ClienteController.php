<?php
require_once __DIR__ . '/../models/Cliente.php';

/**
 * Controller Cliente
 */
class ClienteController
{
    private Cliente $model;

    public function __construct()
    {
        $this->model = new Cliente();
    }

    /**
     * GET /clientes — listado (con búsqueda opcional por ?q=)
     */
    public function index(): void
    {
        requireAuth();

        $q       = trim($_GET['q'] ?? '');
        $clientes = $q !== ''
            ? $this->model->search($q)
            : $this->model->getAll();

        $pageTitle = 'Clientes';
        include __DIR__ . '/../views/clientes/index.php';
    }

    /**
     * GET /clientes/create — formulario de alta
     */
    public function create(): void
    {
        requireAuth();

        $pageTitle = 'Nuevo Cliente';
        include __DIR__ . '/../views/clientes/create.php';
    }

    /**
     * POST /clientes/store — procesa el alta
     */
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clientes');
            return;
        }

        $nombre   = trim($_POST['nombre_apellido'] ?? '');
        $telefono1 = trim($_POST['telefono_1']     ?? '');
        $telefono2 = trim($_POST['telefono_2']     ?? '');

        if ($nombre === '' || $telefono1 === '' || $telefono2 === '') {
            setFlash('danger', 'Los campos Nombre/Apellido, Teléfono 1 y Teléfono 2 son requeridos.');
            redirect('clientes/create');
            return;
        }

        $data = [
            'nombre_apellido' => $nombre,
            'domicilio'       => trim($_POST['domicilio']     ?? ''),
            'localidad'       => trim($_POST['localidad']     ?? ''),
            'codigo_postal'   => trim($_POST['codigo_postal'] ?? '') ?: null,
            'telefono_1'      => $telefono1,
            'telefono_2'      => $telefono2,
            'cuil'            => trim($_POST['cuil']          ?? ''),
        ];

        if ($this->model->create($data)) {
            setFlash('success', 'Cliente creado correctamente.');
            redirect('clientes');
        } else {
            setFlash('danger', 'Error al crear el cliente. Intentá de nuevo.');
            redirect('clientes/create');
        }
    }

    /**
     * GET /clientes/edit/{id} — formulario de edición
     */
    public function edit(int $id): void
    {
        requireAuth();

        $cliente = $this->model->getById($id);
        if (!$cliente) {
            setFlash('warning', 'Cliente no encontrado.');
            redirect('clientes');
            return;
        }

        $pageTitle = 'Editar Cliente';
        include __DIR__ . '/../views/clientes/edit.php';
    }

    /**
     * POST /clientes/update/{id} — procesa la edición
     */
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('clientes');
            return;
        }

        $nombre   = trim($_POST['nombre_apellido'] ?? '');
        $telefono1 = trim($_POST['telefono_1']     ?? '');
        $telefono2 = trim($_POST['telefono_2']     ?? '');

        if ($nombre === '' || $telefono1 === '' || $telefono2 === '') {
            setFlash('danger', 'Los campos Nombre/Apellido, Teléfono 1 y Teléfono 2 son requeridos.');
            redirect('clientes/edit/' . $id);
            return;
        }

        $data = [
            'nombre_apellido' => $nombre,
            'domicilio'       => trim($_POST['domicilio']     ?? ''),
            'localidad'       => trim($_POST['localidad']     ?? ''),
            'codigo_postal'   => trim($_POST['codigo_postal'] ?? '') ?: null,
            'telefono_1'      => $telefono1,
            'telefono_2'      => $telefono2,
            'cuil'            => trim($_POST['cuil']          ?? ''),
        ];

        if ($this->model->update($id, $data)) {
            setFlash('success', 'Cliente actualizado correctamente.');
            redirect('clientes');
        } else {
            setFlash('danger', 'Error al actualizar el cliente. Intentá de nuevo.');
            redirect('clientes/edit/' . $id);
        }
    }

    /**
     * GET|POST /clientes/delete/{id} — elimina el cliente (con chequeo de FK)
     */
    public function delete(int $id): void
    {
        requireAuth();

        if ($this->model->hasVentas($id)) {
            setFlash('warning', 'No se puede eliminar el cliente porque tiene ventas asociadas.');
            redirect('clientes');
            return;
        }

        if ($this->model->delete($id)) {
            setFlash('success', 'Cliente eliminado correctamente.');
        } else {
            setFlash('danger', 'Error al eliminar el cliente.');
        }

        redirect('clientes');
    }
}
