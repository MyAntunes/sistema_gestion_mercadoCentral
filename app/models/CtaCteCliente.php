<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo CtaCteCliente
 */
class CtaCteCliente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todas las cuentas corrientes de un cliente con fecha de la venta.
     */
    public function getByCliente(int $idCliente): array
    {
        $stmt = $this->db->prepare(
            'SELECT ccc.*, v.fecha AS fecha_venta
             FROM cta_cte_cliente ccc
             JOIN ventas v ON ccc.id_venta = v.id_venta
             WHERE v.id_cliente = :id_cliente
             ORDER BY ccc.fecha DESC, ccc.id_cta_cte_cliente DESC'
        );
        $stmt->execute([':id_cliente' => $idCliente]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna la suma de saldos pendientes de todas las cuentas corrientes de un cliente.
     */
    public function getSaldoTotalByCliente(int $idCliente): float
    {
        $stmt = $this->db->prepare(
            'SELECT COALESCE(SUM(ccc.saldo), 0) AS saldo_total
             FROM cta_cte_cliente ccc
             JOIN ventas v ON ccc.id_venta = v.id_venta
             WHERE v.id_cliente = :id_cliente'
        );
        $stmt->execute([':id_cliente' => $idCliente]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Retorna una cuenta corriente por su ID o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ccc.*, v.fecha AS fecha_venta, v.id_cliente, c.nombre_apellido AS cliente_nombre
             FROM cta_cte_cliente ccc
             JOIN ventas v ON ccc.id_venta = v.id_venta
             JOIN cliente c ON v.id_cliente = c.id_cliente
             WHERE ccc.id_cta_cte_cliente = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Retorna la cuenta corriente asociada a una venta específica.
     */
    public function getByVenta(int $idVenta): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cta_cte_cliente WHERE id_venta = :id_venta LIMIT 1'
        );
        $stmt->execute([':id_venta' => $idVenta]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Registra un pago en detalle_cta_cte_cliente y descuenta el saldo en cta_cte_cliente.
     */
    public function registrarPago(int $idCtaCte, float $monto, string $fecha): bool
    {
        try {
            $this->db->beginTransaction();

            $stmtDetalle = $this->db->prepare(
                'INSERT INTO detalle_cta_cte_cliente (monto, fecha, id_cta_cte_cliente)
                 VALUES (:monto, :fecha, :id_cta_cte_cliente)'
            );
            $stmtDetalle->execute([
                ':monto'              => $monto,
                ':fecha'              => $fecha,
                ':id_cta_cte_cliente' => $idCtaCte,
            ]);

            $stmtUpdate = $this->db->prepare(
                'UPDATE cta_cte_cliente
                 SET saldo = saldo - :monto
                 WHERE id_cta_cte_cliente = :id_cta_cte_cliente'
            );
            $stmtUpdate->execute([
                ':monto'              => $monto,
                ':id_cta_cte_cliente' => $idCtaCte,
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    /**
     * Traslada a cuenta corriente la deuda generada por un cheque rechazado.
     * Si la venta ya tiene cta cte suma el monto al saldo; si no, la crea.
     * Registra el detalle con monto NEGATIVO (convención de deuda) vinculado al cheque.
     *
     * IMPORTANTE: NO abre transacción propia; el llamador debe haber hecho beginTransaction()
     * sobre la misma conexión PDO. Lanza excepción ante cualquier error.
     */
    public function registrarDeudaPorChequeRechazado(int $idVenta, float $monto, string $fecha, int $idCheque): void
    {
        if (!$this->db->inTransaction()) {
            throw new LogicException('registrarDeudaPorChequeRechazado requiere una transacción abierta.');
        }

        $ctaCte = $this->getByVenta($idVenta);

        if ($ctaCte === null) {
            $stmtCtaCte = $this->db->prepare(
                'INSERT INTO cta_cte_cliente (fecha, saldo, id_venta)
                 VALUES (:fecha, :saldo, :id_venta)'
            );
            $stmtCtaCte->execute([
                ':fecha'    => $fecha,
                ':saldo'    => $monto,
                ':id_venta' => $idVenta,
            ]);
            $idCtaCte = (int) $this->db->lastInsertId();
        } else {
            $idCtaCte = (int) $ctaCte['id_cta_cte_cliente'];
            $stmtUpdate = $this->db->prepare(
                'UPDATE cta_cte_cliente
                 SET saldo = saldo + :monto
                 WHERE id_cta_cte_cliente = :id_cta_cte_cliente'
            );
            $stmtUpdate->execute([
                ':monto'              => $monto,
                ':id_cta_cte_cliente' => $idCtaCte,
            ]);
        }

        $stmtDetalle = $this->db->prepare(
            'INSERT INTO detalle_cta_cte_cliente (monto, fecha, id_cta_cte_cliente, concepto, id_cheque)
             VALUES (:monto, :fecha, :id_cta_cte_cliente, :concepto, :id_cheque)'
        );
        $stmtDetalle->execute([
            ':monto'              => -$monto,
            ':fecha'              => $fecha,
            ':id_cta_cte_cliente' => $idCtaCte,
            ':concepto'           => 'Cheque #' . $idCheque . ' rechazado',
            ':id_cheque'          => $idCheque,
        ]);
    }

    /**
     * Retorna los movimientos de detalle de una cuenta corriente.
     */
    public function getDetallesByCtaCte(int $idCtaCte): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM detalle_cta_cte_cliente
             WHERE id_cta_cte_cliente = :id_cta_cte
             ORDER BY fecha ASC, id_detalle_cta_cte_cliente ASC'
        );
        $stmt->execute([':id_cta_cte' => $idCtaCte]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna todos los clientes con su deuda en cuenta corriente (saldo_total)
     * y su saldo a favor disponible (saldo_favor).
     */
    public function getClientesConSaldo(): array
    {
        $stmt = $this->db->query(
            "SELECT c.id_cliente, c.nombre_apellido,
                    COALESCE((SELECT SUM(ccc.saldo)
                              FROM cta_cte_cliente ccc
                              JOIN ventas v ON ccc.id_venta = v.id_venta
                              WHERE v.id_cliente = c.id_cliente), 0) AS saldo_total,
                    COALESCE((SELECT SUM(CASE WHEN scm.tipo = 'Ingreso' THEN scm.monto ELSE -scm.monto END)
                              FROM saldo_cliente_movimiento scm
                              WHERE scm.id_cliente = c.id_cliente), 0) AS saldo_favor
             FROM cliente c
             ORDER BY c.nombre_apellido"
        );
        return $stmt->fetchAll();
    }

    /**
     * Retorna todas las cuentas corrientes de un cliente con sus detalles de pago.
     * Cada elemento del array incluye la clave 'detalles' con el array de pagos.
     */
    public function getResumenByCliente(int $idCliente): array
    {
        $cuentas = $this->getByCliente($idCliente);

        foreach ($cuentas as &$cuenta) {
            $cuenta['detalles'] = $this->getDetallesByCtaCte((int) $cuenta['id_cta_cte_cliente']);
        }
        unset($cuenta);

        return $cuentas;
    }
}
