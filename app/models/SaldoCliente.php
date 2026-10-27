<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo SaldoCliente
 * Gestiona los movimientos de saldo a favor de clientes (ingresos por pagos a cuenta y usos en ventas).
 */
class SaldoCliente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna el saldo a favor disponible de un cliente (ingresos - usos).
     */
    public function getSaldoDisponible(int $idCliente): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE -monto END), 0) AS saldo_disponible
             FROM saldo_cliente_movimiento
             WHERE id_cliente = :id_cliente"
        );
        $stmt->execute([':id_cliente' => $idCliente]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Retorna un mapa [id_cliente => saldo_disponible] para todos los clientes con movimientos.
     * @return array<int, float>
     */
    public function getSaldosDisponiblesMap(): array
    {
        $stmt = $this->db->query(
            "SELECT id_cliente,
                    COALESCE(SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE -monto END), 0) AS saldo_disponible
             FROM saldo_cliente_movimiento
             GROUP BY id_cliente"
        );
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row['id_cliente']] = (float) $row['saldo_disponible'];
        }
        return $map;
    }

    /**
     * Retorna todos los movimientos de saldo a favor de un cliente ordenados descendentemente.
     */
    public function getMovimientos(int $idCliente): array
    {
        $stmt = $this->db->prepare(
            'SELECT scm.*, mp.medio_pago AS medio_pago_nombre
             FROM saldo_cliente_movimiento scm
             LEFT JOIN medio_pago mp ON scm.id_medio_pago = mp.id_medio_pago
             WHERE scm.id_cliente = :id_cliente
             ORDER BY scm.fecha DESC, scm.id_saldo_cliente_movimiento DESC'
        );
        $stmt->execute([':id_cliente' => $idCliente]);
        return $stmt->fetchAll();
    }

    /**
     * Registra un ingreso de saldo a favor (pago a cuenta).
     */
    public function registrarIngreso(int $idCliente, float $monto, string $fecha, int $idMedioPago): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO saldo_cliente_movimiento (id_cliente, fecha, tipo, monto, id_medio_pago, id_venta)
             VALUES (:id_cliente, :fecha, 'Ingreso', :monto, :id_medio_pago, NULL)"
        );
        return $stmt->execute([
            ':id_cliente'    => $idCliente,
            ':fecha'         => $fecha,
            ':monto'         => $monto,
            ':id_medio_pago' => $idMedioPago,
        ]);
    }

    /**
     * Registra el uso de saldo a favor en una venta.
     * Requiere una transacción abierta.
     */
    public function registrarUso(int $idCliente, float $monto, string $fecha, int $idVenta): void
    {
        if (!$this->db->inTransaction()) {
            throw new LogicException('registrarUso requiere una transacción abierta.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO saldo_cliente_movimiento (id_cliente, fecha, tipo, monto, id_medio_pago, id_venta)
             VALUES (:id_cliente, :fecha, 'Uso', :monto, NULL, :id_venta)"
        );
        $stmt->execute([
            ':id_cliente' => $idCliente,
            ':fecha'      => $fecha,
            ':monto'      => $monto,
            ':id_venta'   => $idVenta,
        ]);
    }
}
