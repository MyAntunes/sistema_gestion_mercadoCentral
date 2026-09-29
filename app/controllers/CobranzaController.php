<?php
require_once __DIR__ . '/../models/CtaCteCliente.php';
require_once __DIR__ . '/../models/Cliente.php';

/**
 * Controller Cobranzas
 * Gestiona el registro de pagos sobre cuentas corrientes de clientes.
 */
class CobranzaController
{
    private CtaCteCliente $ctaCteModel;
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->ctaCteModel  = new CtaCteCliente();
        $this->clienteModel = new Cliente();
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

        $cuentas     = $this->ctaCteModel->getResumenByCliente($idCliente);
        $saldoTotal  = $this->ctaCteModel->getSaldoTotalByCliente($idCliente);
        $pageTitle   = 'Cuenta Corriente — ' . htmlspecialchars($cliente['nombre_apellido']);

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
            setFlash('danger', 'El monto ($' . number_format($monto, 2) . ') supera el saldo actual ($' . number_format((float) $ctaCte['saldo'], 2) . ').');
            redirect('cobranzas/cliente/' . $idCliente);
            return;
        }

        if ($this->ctaCteModel->registrarPago($idCtaCte, $monto, $fecha)) {
            setFlash('success', 'Pago de $' . number_format($monto, 2) . ' registrado correctamente.');
        } else {
            setFlash('danger', 'Ocurrió un error al registrar el pago. Intentá de nuevo.');
        }

        redirect('cobranzas/cliente/' . $idCliente);
    }
}
