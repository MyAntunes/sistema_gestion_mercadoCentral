<?php
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Proveedor.php';

/**
 * Controller Producto
 */
class ProductoController
{
    private Producto  $model;
    private Proveedor $proveedorModel;

    public function __construct()
    {
        $this->model          = new Producto();
        $this->proveedorModel = new Proveedor();
    }

    /**
     * GET /productos — listado (con búsqueda opcional por ?q=)
     */
    public function index(): void
    {
        requireAuth();

        $q        = trim($_GET['q'] ?? '');
        $productos = $q !== ''
            ? $this->model->search($q)
            : $this->model->getAll();

        $pageTitle = 'Productos';
        include __DIR__ . '/../views/productos/index.php';
    }

    /**
     * GET /productos/create — formulario de alta
     */
    public function create(): void
    {
        requireAuth();

        $proveedores = $this->proveedorModel->getAll();
        $pageTitle   = 'Nuevo Producto';
        include __DIR__ . '/../views/productos/create.php';
    }

    /**
     * POST /productos/store — procesa el alta
     */
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('productos');
            return;
        }

        $nombre       = trim($_POST['nombre']       ?? '');
        $precioCosto  = trim($_POST['precio_costo'] ?? '');
        $precioVenta  = trim($_POST['precio_venta'] ?? '');
        $idProveedor  = trim($_POST['id_proveedor'] ?? '');

        if ($nombre === '' || $precioCosto === '' || $precioVenta === '' || $idProveedor === '') {
            setFlash('danger', 'Los campos Nombre, Precio Costo, Precio Venta y Proveedor son requeridos.');
            redirect('productos/create');
            return;
        }

        if (!is_numeric($precioCosto) || (float) $precioCosto < 0) {
            setFlash('danger', 'El Precio Costo debe ser un número positivo.');
            redirect('productos/create');
            return;
        }

        if (!is_numeric($precioVenta) || (float) $precioVenta < 0) {
            setFlash('danger', 'El Precio Venta debe ser un número positivo.');
            redirect('productos/create');
            return;
        }

        $data = [
            'nombre'       => $nombre,
            'especie'      => trim($_POST['especie'] ?? '') ?: null,
            'precio_costo' => (float) $precioCosto,
            'precio_venta' => (float) $precioVenta,
            'id_proveedor' => (int) $idProveedor,
        ];

        if ($this->model->create($data)) {
            setFlash('success', 'Producto creado correctamente.');
            redirect('productos');
        } else {
            setFlash('danger', 'Error al crear el producto. Intentá de nuevo.');
            redirect('productos/create');
        }
    }

    /**
     * GET /productos/edit/{id} — formulario de edición
     */
    public function edit(int $id): void
    {
        requireAuth();

        $producto = $this->model->getById($id);
        if (!$producto) {
            setFlash('warning', 'Producto no encontrado.');
            redirect('productos');
            return;
        }

        $proveedores = $this->proveedorModel->getAll();
        $pageTitle   = 'Editar Producto';
        include __DIR__ . '/../views/productos/edit.php';
    }

    /**
     * POST /productos/update/{id} — procesa la edición
     */
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('productos');
            return;
        }

        $nombre      = trim($_POST['nombre']       ?? '');
        $precioCosto = trim($_POST['precio_costo'] ?? '');
        $precioVenta = trim($_POST['precio_venta'] ?? '');
        $idProveedor = trim($_POST['id_proveedor'] ?? '');

        if ($nombre === '' || $precioCosto === '' || $precioVenta === '' || $idProveedor === '') {
            setFlash('danger', 'Los campos Nombre, Precio Costo, Precio Venta y Proveedor son requeridos.');
            redirect('productos/edit/' . $id);
            return;
        }

        if (!is_numeric($precioCosto) || (float) $precioCosto < 0) {
            setFlash('danger', 'El Precio Costo debe ser un número positivo.');
            redirect('productos/edit/' . $id);
            return;
        }

        if (!is_numeric($precioVenta) || (float) $precioVenta < 0) {
            setFlash('danger', 'El Precio Venta debe ser un número positivo.');
            redirect('productos/edit/' . $id);
            return;
        }

        $data = [
            'nombre'       => $nombre,
            'especie'      => trim($_POST['especie'] ?? '') ?: null,
            'precio_costo' => (float) $precioCosto,
            'precio_venta' => (float) $precioVenta,
            'id_proveedor' => (int) $idProveedor,
        ];

        if ($this->model->update($id, $data)) {
            setFlash('success', 'Producto actualizado correctamente.');
            redirect('productos');
        } else {
            setFlash('danger', 'Error al actualizar el producto. Intentá de nuevo.');
            redirect('productos/edit/' . $id);
        }
    }

    /**
     * GET|POST /productos/delete/{id} — elimina el producto (con chequeo de FK)
     */
    public function delete(int $id): void
    {
        requireAuth();

        if ($this->model->hasVentas($id)) {
            setFlash('warning', 'No se puede eliminar el producto porque tiene ventas asociadas.');
            redirect('productos');
            return;
        }

        if ($this->model->delete($id)) {
            setFlash('success', 'Producto eliminado correctamente.');
        } else {
            setFlash('danger', 'Error al eliminar el producto.');
        }

        redirect('productos');
    }
}
