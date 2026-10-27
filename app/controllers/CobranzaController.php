<?php
require_once __DIR__ . '/../models/CtaCteCliente.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/SaldoCliente.php';
require_once __DIR__ . '/../models/MedioPago.php';

/**
 * Controller Cobranzas
 * Gestiona el registro de pagos sobre cuentas corrientes de clientes y pagos a cuenta.
 */
class CobranzaController
{
    private CtaCteCliente $ctaCteModel;
    private Cliente $clienteModel;
    private SaldoCliente $saldoClienteModel;
    private MedioPago $medioPagoModel;

    public function __construct()
    {
        $this->ctaCteModel       = new CtaCteCliente();
        $this->clienteModel      = new Cliente();
        $this->saldoClienteModel = new SaldoCliente();
        $this->medioPagoModel    = new MedioPago();
    }

    /**
     * GET /cobranzas — lista clientes con saldo pendiente
     */
    public function index(): void
    {
        requireAuth();

        $clientes  = $this->ctaCteModel->getClientesConSaldo();
        $pageTitle = 'Cobranzas - Cuentas Corrientes';

        include __DIR__ . '/../views/cobranzas/index.php';
    }

    /**
     * GET /cobranzas/cliente/{idCliente} — detalle completo de CTA CTE del cliente
     */
    public function verCliente(int $idCliente): void
    {
        requireAuth();

        $cliente = $this->clienteModel->getById($idCliente);
        if (!$cliente) {
            setFlash('warning', 'Cliente no encontrado.');
            redirect('cobranzas');
            return;
        }

        $cuentas          = $this->ctaCteModel->getResumenByCliente($idCliente);
        $saldoTotal       = $this->ctaCteModel->getSaldoTotalByCliente($idCliente);
        $saldoFavor       = $this->saldoClienteModel->getSaldoDisponible($idCliente);
        $movimientosSaldo = $this->saldoClienteModel->getMovimientos($idCliente);

        $mediosPagoAll = $this->medioPagoModel->getAll();
        $mediosIngreso = array_values(array_filter($mediosPagoAll, function ($mp) {
            return !in_array($mp['medio_pago'], ['Cuenta Corriente', 'Saldo a favor'], true);
        }));

        $pageTitle = 'Cuenta Corriente — ' . htmlspecialchars($cliente['nombre_apellido']);

        include __DIR__ . '/../views/cobranzas/ver_cliente.php';
    }

    /**
     * POST /cobranzas/registrar-pago — registra un pago en una cuenta corriente
     */
    public function registrarPago(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cobranzas');
            return;
        }

        $idCtaCte  = (int) trim($_POST['id_cta_cte_cliente'] ?? 0);
        $idCliente = (int) trim($_POST['id_cliente']         ?? 0);
        $monto     = (float) trim($_POST['monto']            ?? 0);
        $fecha     = trim($_POST['fecha'] ?? date('Y-m-d'));

        if ($idCtaCte <= 0 || $idCliente <= 0) {
            setFlash('danger', 'Datos incompletos. Intentá de nuevo.');
            redirect('cobranzas');
            return;
        }

        if ($monto <= 0) {
            setFlash('danger', 'El monto debe ser mayor a cero.');
            redirect('cobranzas/cliente/' . $idCliente);
            return;
        }

        // Verificar que el monto no supere el saldo actual de esa cuenta
        $ctaCte = $this->ctaCteModel->getById($idCtaCte);
        if (!$ctaCte) {
            setFlash('danger', 'Cuenta corriente no encontrada.');
            redirect('cobranzas/cliente/' . $idCliente);
            return;
        }

        if ($monto > (float) $ctaCte['saldo']) {
            setFlash('danger', 'El monto ($' . number_format($monto, 0) . ') supera el saldo actual ($' . number_format((float) $ctaCte['saldo'], 0) . ').');
            redirect('cobranzas/cliente/' . $idCliente);
            return;
        }

        if ($this->ctaCteModel->registrarPago($idCtaCte, $monto, $fecha)) {
            setFlash('success', 'Pago de $' . number_format($monto, 0) . ' registrado correctamente.');
        } else {
            setFlash('danger', 'Ocurrió un error al registrar el pago. Intentá de nuevo.');
        }

        redirect('cobranzas/cliente/' . $idCliente);
    }

    /**
     * POST /cobranzas/registrar-pago-a-cuenta — registra un ingreso de saldo a favor del cliente
     */
    public function registrarPagoACuenta(): void
    {
        requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cobranzas');
            return;
        }

        $idCliente   = (int) trim($_POST['id_cliente'] ?? 0);
        $monto       = (float) trim($_POST['monto'] ?? 0);
        $fecha       = trim($_POST['fecha'] ?? date('Y-m-d'));
        $idMedioPago = (int) trim($_POST['id_medio_pago'] ?? 0);

        if ($idCliente <= 0) {
            setFlash('danger', 'Cliente no especificado.');
            redirect('cobranzas');
            return;
        }

        $cliente = $this->clienteModel->getById($idCliente);
        if (!$cliente) {
            setFlash('danger', 'Cliente no encontrado.');
            redirect('cobranzas');
            return;
        }

        if ($monto <= 0) {
            setFlash('danger', 'El monto debe ser mayor a cero.');
            redirect('cobranzas/cliente/' . $idCliente);
            return;
        }

        $medio = $this->medioPagoModel->getById($idMedioPago);
        if (!$medio || in_array($medio['medio_pago'], ['Cuenta Corriente', 'Saldo a favor'], true)) {
            setFlash('danger', 'Medio de pago no válido para ingresar dinero a cuenta.');
            redirect('cobranzas/cliente/' . $idCliente);
            return;
        }

        if ($this->saldoClienteModel->registrarIngreso($idCliente, $monto, $fecha, $idMedioPago)) {
            setFlash('success', 'Pago a cuenta de $' . number_format($monto, 0) . ' registrado correctamente.');
        } else {
            setFlash('danger', 'Ocurrió un error al registrar el pago a cuenta. Intentá de nuevo.');
        }

        redirect('cobranzas/cliente/' . $idCliente);
    }
}
