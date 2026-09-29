<?php
require_once __DIR__ . '/../models/Venta.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/MedioPago.php';
require_once __DIR__ . '/../models/CtaCteCliente.php';

/**
 * Controller Venta
 */
class VentaController
{
    private Venta $model;
    private Cliente $clienteModel;
    private Producto $productoModel;
    private MedioPago $medioPagoModel;
    private CtaCteCliente $ctaCteModel;

    public function __construct()
    {
        $this->model          = new Venta();
        $this->clienteModel   = new Cliente();
        $this->productoModel  = new Producto();
        $this->medioPagoModel = new MedioPago();
        $this->ctaCteModel    = new CtaCteCliente();
    }

    /**
     * GET /ventas — listado con filtros opcionales por fecha y cliente
     */
    public function index(): void
    {
        requireAuth();

        $filtros = [
            'fecha_desde' => trim($_GET['fecha_desde'] ?? ''),
            'fecha_hasta' => trim($_GET['fecha_hasta'] ?? ''),
            'id_cliente'  => trim($_GET['id_cliente'] ?? ''),
        ];

        $ventas   = $this->model->getAll($filtros);
        $clientes = $this->clienteModel->getAll();

        $pageTitle = 'Ventas';
        include __DIR__ . '/../views/ventas/index.php';
    }

    /**
     * GET /ventas/create — formulario de registro de venta
     */
    public function create(): void
    {
        requireAuth();

        $clientes   = $this->clienteModel->getAll();
        $productos  = $this->productoModel->getAll();
        $mediosPago = $this->medioPagoModel->getAll();

        $pageTitle  = 'Nueva Venta';
        include __DIR__ . '/../views/ventas/create.php';
    }

    /**
     * POST /ventas/store — procesa y guarda la nueva venta
     */
    public function store(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('ventas');
            return;
        }

        $idCliente = trim($_POST['id_cliente'] ?? '');
        $fecha     = trim($_POST['fecha'] ?? date('Y-m-d'));
        $detalles  = $_POST['detalle'] ?? [];
        $pagosPost = $_POST['pago'] ?? [];

        if ($idCliente === '' || empty($detalles) || empty($pagosPost)) {
            setFlash('danger', 'Debe seleccionar un cliente, al menos un producto y al menos un medio de pago.');
            redirect('ventas/create');
            return;
        }

        // Cargar mapa de medios de pago por ID para conocer su nombre exacto
        $allMedios = $this->medioPagoModel->getAll();
        $mediosMap = [];
        foreach ($allMedios as $mp) {
            $mediosMap[$mp['id_medio_pago']] = $mp['medio_pago'];
        }

        // Validar y limpiar productos
        $detallesLimpios = [];
        $totalVenta = 0.0;
        foreach ($detalles as $det) {
            $idProd   = (int) ($det['id_producto'] ?? 0);
            $cant     = (int) ($det['cantidad'] ?? 0);
            $precioU  = (float) ($det['precio_unitario'] ?? 0);
            $desc     = isset($det['descuento']) && $det['descuento'] !== '' ? (float) $det['descuento'] : 0.0;

            if ($idProd <= 0 || $cant <= 0 || $precioU < 0) {
                continue;
            }

            $subtotal = $precioU * $cant * (1 - ($desc / 100));
            $totalVenta += $subtotal;

            $detallesLimpios[] = [
                'id_producto'     => $idProd,
                'cantidad'        => $cant,
                'precio_unitario' => $precioU,
                'descuento'       => $desc,
            ];
        }

        if (empty($detallesLimpios)) {
            setFlash('danger', 'Debe agregar al menos un producto válido.');
            redirect('ventas/create');
            return;
        }

        // Validar y estructurar pagos
        $pagosLimpios = [];
        $totalPagos = 0.0;
        foreach ($pagosPost as $pago) {
            $idMedio = (int) ($pago['id_medio_pago'] ?? 0);
            $monto   = (float) ($pago['monto'] ?? 0);

            if ($idMedio <= 0 || $monto <= 0) {
                continue;
            }

            $totalPagos += $monto;
            $nombreMedio = $mediosMap[$idMedio] ?? '';

            $pagoItem = [
                'id_medio_pago'     => $idMedio,
                'medio_pago_nombre' => $nombreMedio,
                'monto'             => $monto,
                'cheque'            => null,
            ];

            if ($nombreMedio === 'Cheque' || $nombreMedio === 'E-cheque') {
                $chequePost = $pago['cheque'] ?? [];
                $pagoItem['cheque'] = [
                    'banco'          => trim($chequePost['banco'] ?? ''),
                    'numero_cheque'  => (int) ($chequePost['numero_cheque'] ?? 0),
                    'fecha_emision'  => trim($chequePost['fecha_emision'] ?? $fecha),
                    'fecha_pago'     => trim($chequePost['fecha_pago'] ?? $fecha),
                    'tipo_cheque'    => trim($chequePost['tipo_cheque'] ?? ($nombreMedio === 'E-cheque' ? 'E-cheque' : 'Físico')),
                    'observaciones'  => trim($chequePost['observaciones'] ?? ''),
                ];
            }

            $pagosLimpios[] = $pagoItem;
        }

        if (empty($pagosLimpios)) {
            setFlash('danger', 'Debe ingresar al menos un medio de pago válido.');
            redirect('ventas/create');
            return;
        }

        // Validación de cuadre de caja (tolerancia $0.05 por redondeo)
        if (abs($totalPagos - $totalVenta) > 0.05) {
            setFlash('danger', 'El total de los medios de pago ($' . number_format($totalPagos, 2) . ') no coincide con el total de la venta ($' . number_format($totalVenta, 2) . ').');
            redirect('ventas/create');
            return;
        }

        $ventaData = [
            'fecha'      => $fecha,
            'id_cliente' => (int) $idCliente,
            'id_usuario' => (int) $_SESSION['id_usuario'],
        ];

        $idVenta = $this->model->create($ventaData, $detallesLimpios, $pagosLimpios);

        if ($idVenta !== false) {
            setFlash('success', 'Venta #' . $idVenta . ' registrada con éxito.');
            redirect('ventas/show/' . $idVenta);
        } else {
            setFlash('danger', 'Ocurrió un error al guardar la venta. Intente nuevamente.');
            redirect('ventas/create');
        }
    }

    /**
     * GET /ventas/show/{id} — detalle completo de la venta
     */
    public function show(int $id): void
    {
        requireAuth();

        $venta = $this->model->getById($id);
        if (!$venta) {
            setFlash('warning', 'Venta no encontrada.');
            redirect('ventas');
            return;
        }

        $detalles   = $this->model->getDetalleById($id);
        $mediosPago = $this->model->getMediosPagoById($id);
        $ctaCte     = $this->ctaCteModel->getByVenta($id);

        $pageTitle = 'Detalle de Venta #' . $id;
        include __DIR__ . '/../views/ventas/show.php';
    }
}
