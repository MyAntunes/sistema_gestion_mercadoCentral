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
     * Retorna todos los clientes que tienen saldo > 0 en cta_cte_cliente,
     * con el saldo total acumulado.
     */
    public function getClientesConSaldo(): array
    {
        $stmt = $this->db->query(
            'SELECT c.id_cliente, c.nombre_apellido, SUM(ccc.saldo) AS saldo_total
             FROM cta_cte_cliente ccc
             JOIN ventas v ON ccc.id_venta = v.id_venta
             JOIN cliente c ON v.id_cliente = c.id_cliente
             WHERE ccc.saldo > 0
             GROUP BY c.id_cliente, c.nombre_apellido
             ORDER BY c.nombre_apellido'
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
