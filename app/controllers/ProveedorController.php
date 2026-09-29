<?php
require_once __DIR__ . '/../models/Proveedor.php';

/**
 * Controller Proveedor
 */
class ProveedorController
{
    private Proveedor $model;

    public function __construct()
    {
        $this->model = new Proveedor();
    }

    /**
     * GET /proveedores — listado (con búsqueda opcional por ?q=)
     */
    public function index(): void
    {
        requireAuth();

        $q          = trim($_GET['q'] ?? '');
        $proveedores = $q !== ''
            ? $this->model->search($q)
            : $this->model->getAll();

        $pageTitle = 'Proveedores';
        include __DIR__ . '/../views/proveedores/index.php';
    }

    /**
     * GET /proveedores/create — formulario de alta
     */
    public function create(): void
    {
        requireAuth();

        $pageTitle = 'Nuevo Proveedor';
        include __DIR__ . '/../views/proveedores/create.php';
    }

    /**
     * POST /proveedores/store — procesa el alta
     */
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('proveedores');
            return;
        }

        $razonSocial = trim($_POST['razon_social'] ?? '');
        $telefono1   = trim($_POST['telefono_1']   ?? '');
        $telefono2   = trim($_POST['telefono_2']   ?? '');

        if ($razonSocial === '' || $telefono1 === '' || $telefono2 === '') {
            setFlash('danger', 'Los campos Razón Social, Teléfono 1 y Teléfono 2 son requeridos.');
            redirect('proveedores/create');
            return;
        }

        $data = [
            'razon_social' => $razonSocial,
            'direccion'    => trim($_POST['direccion'] ?? '') ?: null,
            'telefono_1'   => $telefono1,
            'telefono_2'   => $telefono2,
            'cuit'         => trim($_POST['cuit']      ?? '') ?: null,
        ];

        if ($this->model->create($data)) {
            setFlash('success', 'Proveedor creado correctamente.');
            redirect('proveedores');
        } else {
            setFlash('danger', 'Error al crear el proveedor. Intentá de nuevo.');
            redirect('proveedores/create');
        }
    }

    /**
     * GET /proveedores/edit/{id} — formulario de edición
     */
    public function edit(int $id): void
    {
        requireAuth();

        $proveedor = $this->model->getById($id);
        if (!$proveedor) {
            setFlash('warning', 'Proveedor no encontrado.');
            redirect('proveedores');
            return;
        }

        $pageTitle = 'Editar Proveedor';
        include __DIR__ . '/../views/proveedores/edit.php';
    }

    /**
     * POST /proveedores/update/{id} — procesa la edición
     */
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('proveedores');
            return;
        }

        $razonSocial = trim($_POST['razon_social'] ?? '');
        $telefono1   = trim($_POST['telefono_1']   ?? '');
        $telefono2   = trim($_POST['telefono_2']   ?? '');

        if ($razonSocial === '' || $telefono1 === '' || $telefono2 === '') {
            setFlash('danger', 'Los campos Razón Social, Teléfono 1 y Teléfono 2 son requeridos.');
            redirect('proveedores/edit/' . $id);
            return;
        }

        $data = [
            'razon_social' => $razonSocial,
            'direccion'    => trim($_POST['direccion'] ?? '') ?: null,
            'telefono_1'   => $telefono1,
            'telefono_2'   => $telefono2,
            'cuit'         => trim($_POST['cuit']      ?? '') ?: null,
        ];

        if ($this->model->update($id, $data)) {
            setFlash('success', 'Proveedor actualizado correctamente.');
            redirect('proveedores');
        } else {
            setFlash('danger', 'Error al actualizar el proveedor. Intentá de nuevo.');
            redirect('proveedores/edit/' . $id);
        }
    }

    /**
     * GET|POST /proveedores/delete/{id} — elimina el proveedor (con chequeo de FK)
     */
    public function delete(int $id): void
    {
        requireAuth();

        if ($this->model->hasProductos($id)) {
            setFlash('warning', 'No se puede eliminar el proveedor porque tiene productos asociados.');
            redirect('proveedores');
            return;
        }

        if ($this->model->delete($id)) {
            setFlash('success', 'Proveedor eliminado correctamente.');
        } else {
            setFlash('danger', 'Error al eliminar el proveedor.');
        }

        redirect('proveedores');
    }
}
