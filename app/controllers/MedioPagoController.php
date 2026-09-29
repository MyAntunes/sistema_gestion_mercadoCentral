<?php
require_once __DIR__ . '/../models/MedioPago.php';

/**
 * Controller MedioPago
 */
class MedioPagoController
{
    private MedioPago $model;

    public function __construct()
    {
        $this->model = new MedioPago();
    }

    /**
     * GET /medios-pago — listado de medios de pago
     */
    public function index(): void
    {
        requireAuth();

        $mediosPago = $this->model->getAll();
        $pageTitle  = 'Medios de Pago';
        include __DIR__ . '/../views/medios_pago/index.php';
    }

    /**
     * GET /medios-pago/create — formulario de alta
     */
    public function create(): void
    {
        requireAuth();

        $enumValues = $this->model->getEnumValues();
        $pageTitle  = 'Nuevo Medio de Pago';
        include __DIR__ . '/../views/medios_pago/create.php';
    }

    /**
     * POST /medios-pago/store — procesa el alta
     */
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('medios-pago');
            return;
        }

        $medioPago  = trim($_POST['medio_pago'] ?? '');
        $enumValues = $this->model->getEnumValues();

        if ($medioPago === '') {
            setFlash('danger', 'El campo Medio de Pago es requerido.');
            redirect('medios-pago/create');
            return;
        }

        if (!in_array($medioPago, $enumValues, true)) {
            setFlash('danger', 'El valor seleccionado no es un medio de pago válido.');
            redirect('medios-pago/create');
            return;
        }

        if ($this->model->create(['medio_pago' => $medioPago])) {
            setFlash('success', 'Medio de pago creado correctamente.');
            redirect('medios-pago');
        } else {
            setFlash('danger', 'Error al crear el medio de pago. Intentá de nuevo.');
            redirect('medios-pago/create');
        }
    }

    /**
     * GET /medios-pago/edit/{id} — formulario de edición
     */
    public function edit(int $id): void
    {
        requireAuth();

        $medioPago = $this->model->getById($id);
        if (!$medioPago) {
            setFlash('warning', 'Medio de pago no encontrado.');
            redirect('medios-pago');
            return;
        }

        $enumValues = $this->model->getEnumValues();
        $pageTitle  = 'Editar Medio de Pago';
        include __DIR__ . '/../views/medios_pago/edit.php';
    }

    /**
     * POST /medios-pago/update/{id} — procesa la edición
     */
    public function update(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('medios-pago');
            return;
        }

        $medioPago  = trim($_POST['medio_pago'] ?? '');
        $enumValues = $this->model->getEnumValues();

        if ($medioPago === '') {
            setFlash('danger', 'El campo Medio de Pago es requerido.');
            redirect('medios-pago/edit/' . $id);
            return;
        }

        if (!in_array($medioPago, $enumValues, true)) {
            setFlash('danger', 'El valor seleccionado no es un medio de pago válido.');
            redirect('medios-pago/edit/' . $id);
            return;
        }

        if ($this->model->update($id, ['medio_pago' => $medioPago])) {
            setFlash('success', 'Medio de pago actualizado correctamente.');
            redirect('medios-pago');
        } else {
            setFlash('danger', 'Error al actualizar el medio de pago. Intentá de nuevo.');
            redirect('medios-pago/edit/' . $id);
        }
    }

    /**
     * GET|POST /medios-pago/delete/{id} — elimina el medio de pago (con chequeo de FK)
     */
    public function delete(int $id): void
    {
        requireAuth();

        if ($this->model->hasVentas($id)) {
            setFlash('warning', 'No se puede eliminar el medio de pago porque tiene ventas asociadas.');
            redirect('medios-pago');
            return;
        }

        if ($this->model->delete($id)) {
            setFlash('success', 'Medio de pago eliminado correctamente.');
        } else {
            setFlash('danger', 'Error al eliminar el medio de pago.');
        }

        redirect('medios-pago');
    }
}
