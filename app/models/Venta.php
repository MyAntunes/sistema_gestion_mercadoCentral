<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo Venta
 */
class Venta
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna el listado de ventas con datos de cliente y usuario.
     * Filtros opcionales: id_cliente, fecha_desde, fecha_hasta.
     */
    public function getAll(array $filtros = []): array
    {
        $sql = 'SELECT v.*, c.nombre_apellido, u.nombre_usuario,
                       (SELECT COALESCE(SUM(dv.precio_unitario * dv.cantidad * (1 - COALESCE(dv.descuento, 0) / 100)), 0)
                        FROM detalle_ventas dv WHERE dv.id_venta = v.id_venta) AS total
                FROM ventas v
                JOIN cliente c ON v.id_cliente = c.id_cliente
                JOIN usuario u ON v.id_usuario = u.id_usuario
                WHERE 1=1';

        $params = [];

        if (!empty($filtros['id_cliente'])) {
            $sql .= ' AND v.id_cliente = :id_cliente';
            $params[':id_cliente'] = (int) $filtros['id_cliente'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= ' AND v.fecha >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= ' AND v.fecha <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= ' ORDER BY v.fecha DESC, v.id_venta DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Retorna una venta por ID con JOINs a cliente y usuario.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT v.*, c.nombre_apellido, c.telefono_1, c.domicilio, c.cuil, u.nombre_usuario
             FROM ventas v
             JOIN cliente c ON v.id_cliente = c.id_cliente
             JOIN usuario u ON v.id_usuario = u.id_usuario
             WHERE v.id_venta = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Retorna el detalle de productos de una venta.
     */
    public function getDetalleById(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT dv.*, p.nombre AS producto_nombre, p.especie AS producto_especie
             FROM detalle_ventas dv
             JOIN producto p ON dv.id_producto = p.id_producto
             WHERE dv.id_venta = :id_venta
             ORDER BY dv.id_detalle_ventas ASC'
        );
        $stmt->execute([':id_venta' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna los medios de pago asociados a una venta.
     */
    public function getMediosPagoById(int $id): array
    {
        $stmt = $this->db->prepare(
            'SELECT vmp.*, mp.medio_pago AS medio_pago_nombre,
                    ch.id_cheque, ch.numero_cheque, ch.banco, ch.fecha_pago, ch.fecha_emision, ch.tipo_cheque, ch.estado AS cheque_estado, ch.observaciones AS cheque_observaciones
             FROM venta_medio_pago vmp
             JOIN medio_pago mp ON vmp.id_medio_pago = mp.id_medio_pago
             LEFT JOIN venta_medio_pago_cheque vmpc ON vmp.id_venta_medio_pago = vmpc.id_venta_medio_pago
             LEFT JOIN cheque ch ON vmpc.id_cheque = ch.id_cheque
             WHERE vmp.id_venta = :id_venta
             ORDER BY vmp.id_venta_medio_pago ASC'
        );
        $stmt->execute([':id_venta' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Registra una venta completa con sus detalles y medios de pago dentro de una transacción.
     *
     * @param array $ventaData ['fecha' => string, 'id_cliente' => int, 'id_usuario' => int]
     * @param array $detalles  Array de items ['id_producto' => int, 'cantidad' => int, 'precio_unitario' => float, 'descuento' => float]
     * @param array $pagos     Array de pagos ['id_medio_pago' => int, 'medio_pago_nombre' => string, 'monto' => float, 'cheque' => array|null]
     * @return int|false Retorna el id_venta generado o false si falla.
     */
    public function create(array $ventaData, array $detalles, array $pagos)
    {
        try {
            $this->db->beginTransaction();

            // 1. Insertar cabecera de venta
            $stmtVenta = $this->db->prepare(
                'INSERT INTO ventas (fecha, id_cliente, id_usuario)
                 VALUES (:fecha, :id_cliente, :id_usuario)'
            );
            $stmtVenta->execute([
                ':fecha'      => $ventaData['fecha'],
                ':id_cliente' => (int) $ventaData['id_cliente'],
                ':id_usuario' => (int) $ventaData['id_usuario'],
            ]);
            $idVenta = (int) $this->db->lastInsertId();

            // 2. Insertar detalle de productos
            $stmtDetalle = $this->db->prepare(
                'INSERT INTO detalle_ventas (precio_unitario, cantidad, descuento, id_producto, id_venta)
                 VALUES (:precio_unitario, :cantidad, :descuento, :id_producto, :id_venta)'
            );

            foreach ($detalles as $det) {
                $descuento = isset($det['descuento']) && $det['descuento'] !== '' ? (float) $det['descuento'] : 0.00;
                $stmtDetalle->execute([
                    ':precio_unitario' => (float) $det['precio_unitario'],
                    ':cantidad'        => (int) $det['cantidad'],
                    ':descuento'       => $descuento,
                    ':id_producto'     => (int) $det['id_producto'],
                    ':id_venta'        => $idVenta,
                ]);
            }

            // 3. Insertar medios de pago y sub-estructuras (cheques / cuenta corriente)
            $stmtVmp = $this->db->prepare(
                'INSERT INTO venta_medio_pago (id_venta, id_medio_pago, monto)
                 VALUES (:id_venta, :id_medio_pago, :monto)'
            );

            $stmtCheque = $this->db->prepare(
                'INSERT INTO cheque (fecha_pago, fecha_emision, numero_cheque, banco, monto, tipo_cheque, estado, observaciones, id_medio_pago)
                 VALUES (:fecha_pago, :fecha_emision, :numero_cheque, :banco, :monto, :tipo_cheque, :estado, :observaciones, :id_medio_pago)'
            );

            $stmtVmpc = $this->db->prepare(
                'INSERT INTO venta_medio_pago_cheque (id_cheque, id_venta_medio_pago)
                 VALUES (:id_cheque, :id_venta_medio_pago)'
            );

            $stmtCtaCte = $this->db->prepare(
                'INSERT INTO cta_cte_cliente (fecha, saldo, id_venta)
                 VALUES (:fecha, :saldo, :id_venta)'
            );

            $stmtDetalleCtaCte = $this->db->prepare(
                'INSERT INTO detalle_cta_cte_cliente (monto, fecha, id_cta_cte_cliente)
                 VALUES (:monto, :fecha, :id_cta_cte_cliente)'
            );

            foreach ($pagos as $pago) {
                $monto = (float) $pago['monto'];
                $idMedioPago = (int) $pago['id_medio_pago'];
                $nombreMedio = trim($pago['medio_pago_nombre'] ?? '');

                // a. Insertar en venta_medio_pago
                $stmtVmp->execute([
                    ':id_venta'      => $idVenta,
                    ':id_medio_pago' => $idMedioPago,
                    ':monto'         => $monto,
                ]);
                $idVmp = (int) $this->db->lastInsertId();

                // b. Si es Cheque o E-cheque
                if ($nombreMedio === 'Cheque' || $nombreMedio === 'E-cheque') {
                    $chequeData = $pago['cheque'] ?? [];
                    $tipoCheque = ($nombreMedio === 'E-cheque' || ($chequeData['tipo_cheque'] ?? '') === 'E-cheque') ? 'E-cheque' : 'Físico';
                    $fechaEmision = !empty($chequeData['fecha_emision']) ? $chequeData['fecha_emision'] : $ventaData['fecha'];
                    $fechaPago = !empty($chequeData['fecha_pago']) ? $chequeData['fecha_pago'] : $ventaData['fecha'];
                    $numeroCheque = (int) ($chequeData['numero_cheque'] ?? 0);
                    $banco = trim($chequeData['banco'] ?? '');
                    $observaciones = trim($chequeData['observaciones'] ?? '') ?: null;

                    $stmtCheque->execute([
                        ':fecha_pago'     => $fechaPago,
                        ':fecha_emision'  => $fechaEmision,
                        ':numero_cheque'  => $numeroCheque,
                        ':banco'          => $banco,
                        ':monto'          => $monto,
                        ':tipo_cheque'    => $tipoCheque,
                        ':estado'         => 'Cartera',
                        ':observaciones'  => $observaciones,
                        ':id_medio_pago'  => $idMedioPago,
                    ]);
                    $idCheque = (int) $this->db->lastInsertId();

                    $stmtVmpc->execute([
                        ':id_cheque'             => $idCheque,
                        ':id_venta_medio_pago'   => $idVmp,
                    ]);
                }

                // c. Si es Cuenta Corriente
                if ($nombreMedio === 'Cuenta Corriente') {
                    $stmtCtaCte->execute([
                        ':fecha'    => $ventaData['fecha'],
                        ':saldo'    => $monto,
                        ':id_venta' => $idVenta,
                    ]);
                    $idCtaCte = (int) $this->db->lastInsertId();

                    // Registrar en detalle_cta_cte_cliente con monto inicial de deuda
                    $stmtDetalleCtaCte->execute([
                        ':monto'              => -$monto, // o el saldo inicial de deuda
                        ':fecha'              => $ventaData['fecha'],
                        ':id_cta_cte_cliente' => $idCtaCte,
                    ]);
                }
            }

            $this->db->commit();
            return $idVenta;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Error en Venta::create: ' . $e->getMessage());
            return false;
        }
    }
}
