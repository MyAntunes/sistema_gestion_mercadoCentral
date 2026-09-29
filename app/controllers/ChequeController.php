<?php
require_once __DIR__ . '/../models/Cheque.php';

class ChequeController
{
    private Cheque $model;

    public function __construct()
    {
        $this->model = new Cheque();
    }

    /**
     * Listado de cheques con filtros opcionales por estado, fecha_desde, fecha_hasta.
     */
    public function index(): void
    {
        requireAuth();

        $filtros = [
            'estado'      => $_GET['estado']      ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
        ];

        // Limpiar filtros vacíos para que no afecten la query
        $filtros = array_filter($filtros, fn($v) => $v !== '');

        $cheques = $this->model->getAll($filtros);

        // Restaurar para el formulario (con strings vacíos)
        $filtros = array_merge(['estado' => '', 'fecha_desde' => '', 'fecha_hasta' => ''], $filtros);

        require __DIR__ . '/../views/cheques/index.php';
    }

    /**
     * Actualiza el estado de un cheque. Solo acepta POST.
     */
    public function updateEstado(int $id): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cheques');
        }

        $nuevoEstado = $_POST['estado'] ?? '';

        $ok = $this->model->updateEstado($id, $nuevoEstado);

        if ($ok) {
            setFlash('success', 'Estado del cheque actualizado correctamente.');
        } else {
            setFlash('error', 'No se pudo actualizar el estado. La transición no es válida.');
        }

        redirect('cheques');
    }
}
