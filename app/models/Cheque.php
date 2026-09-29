<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo Cheque
 */
class Cheque
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los cheques con JOIN a venta_medio_pago_cheque, venta_medio_pago y ventas.
     * Filtros opcionales: estado, fecha_desde, fecha_hasta.
     */
    public function getAll(array $filtros = []): array
    {
        $sql = 'SELECT ch.*, vmp.id_venta, mp.medio_pago,
                       c.nombre_apellido AS cliente,
                       v.fecha AS fecha_venta
                FROM cheque ch
                JOIN venta_medio_pago_cheque vmpc ON ch.id_cheque = vmpc.id_cheque
                JOIN venta_medio_pago vmp ON vmpc.id_venta_medio_pago = vmp.id_venta_medio_pago
                JOIN ventas v ON vmp.id_venta = v.id_venta
                JOIN cliente c ON v.id_cliente = c.id_cliente
                JOIN medio_pago mp ON ch.id_medio_pago = mp.id_medio_pago
                WHERE 1=1';

        $params = [];

        if (!empty($filtros['estado'])) {
            $sql .= ' AND ch.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND ch.fecha_pago >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND ch.fecha_pago <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= ' ORDER BY ch.fecha_pago ASC, ch.id_cheque DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Retorna un cheque por ID con información de la venta asociada o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ch.*, vmp.id_venta, mp.medio_pago,
                    c.nombre_apellido AS cliente,
                    v.fecha AS fecha_venta
             FROM cheque ch
             JOIN venta_medio_pago_cheque vmpc ON ch.id_cheque = vmpc.id_cheque
             JOIN venta_medio_pago vmp ON vmpc.id_venta_medio_pago = vmp.id_venta_medio_pago
             JOIN ventas v ON vmp.id_venta = v.id_venta
             JOIN cliente c ON v.id_cliente = c.id_cliente
             JOIN medio_pago mp ON ch.id_medio_pago = mp.id_medio_pago
             WHERE ch.id_cheque = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Actualiza el estado de un cheque validando la transición permitida.
     */
    public function updateEstado(int $id, string $estado): bool
    {
        $estadosValidos = ['Cartera', 'Depositado', 'Cobrado', 'Rechazado'];
        if (!in_array($estado, $estadosValidos, true)) {
            return false;
        }

        $cheque = $this->getById($id);
        if ($cheque === null) {
            return false;
        }

        $permitidos = $this->getEstadosPermitidos($cheque['estado']);
        if (!in_array($estado, $permitidos, true)) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE cheque SET estado = :estado WHERE id_cheque = :id');
        return $stmt->execute([
            ':estado' => $estado,
            ':id'     => $id,
        ]);
    }

    /**
     * Retorna los estados a los que puede transicionar el cheque según su estado actual.
     */
    public function getEstadosPermitidos(string $estadoActual): array
    {
        $transiciones = [
            'Cartera'    => ['Depositado'],
            'Depositado' => ['Cobrado', 'Rechazado'],
            'Cobrado'    => [],
            'Rechazado'  => [],
        ];

        return $transiciones[$estadoActual] ?? [];
    }
}
