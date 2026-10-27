<?php
/**
 * Dashboard — Modelo con indicadores del sistema.
 *
 * Uso:
 *   $dashboard = new Dashboard();
 *   $indicadores = $dashboard->getIndicadores();
 */
class Dashboard
{
    /** @var PDO */
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna un array asociativo con los indicadores clave del sistema.
     *
     * @return array<string, int|float>
     */
    public function getIndicadores(): array
    {
        $queries = [
            'total_clientes'        => 'SELECT COUNT(*) FROM cliente',
            'total_proveedores'     => 'SELECT COUNT(*) FROM proveedor',
            'total_productos'       => 'SELECT COUNT(*) FROM producto',
            'total_ventas'          => 'SELECT COUNT(*) FROM ventas',
            'monto_total_ventas'    => 'SELECT COALESCE(SUM(monto), 0) FROM venta_medio_pago',
            'clientes_con_cta_cte'  => 'SELECT COUNT(DISTINCT id_venta) FROM cta_cte_cliente WHERE saldo > 0',
            'cheques_en_cartera'    => "SELECT COUNT(*) FROM cheque WHERE estado = 'Cartera'",
            'ventas_hoy'            => 'SELECT COUNT(*) FROM ventas WHERE fecha = CURDATE()',
        ];

        $indicadores = [];

        foreach ($queries as $key => $sql) {
            $stmt = $this->db->query($sql);
            $indicadores[$key] = $stmt->fetchColumn();
        }

        return $indicadores;
    }

    /**
     * Retorna datos para los gráficos de ventas según el período solicitado.
     *
     * @param string $periodo  'dia' | 'semana' | 'mes' | 'anio'
     * @return array{labels: string[], montos: float[], cantidades: int[]}
     */
    public function getGraficosData(string $periodo): array
    {
        switch ($periodo) {
            case 'dia':
                // Ventas del día actual agrupadas por hora
                $sql = "
                    SELECT
                        DATE_FORMAT(v.fecha, '%H:00') AS label,
                        COALESCE(SUM(vmp.monto), 0)   AS monto,
                        COUNT(DISTINCT v.id_venta)     AS cantidad
                    FROM ventas v
                    LEFT JOIN venta_medio_pago vmp ON vmp.id_venta = v.id_venta
                    WHERE v.fecha = CURDATE()
                    GROUP BY DATE_FORMAT(v.fecha, '%H:00')
                    ORDER BY label ASC
                ";
                break;

            case 'semana':
                // Últimos 7 días
                $sql = "
                    SELECT
                        DATE_FORMAT(v.fecha, '%d/%m') AS label,
                        COALESCE(SUM(vmp.monto), 0)   AS monto,
                        COUNT(DISTINCT v.id_venta)     AS cantidad
                    FROM ventas v
                    LEFT JOIN venta_medio_pago vmp ON vmp.id_venta = v.id_venta
                    WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    GROUP BY v.fecha
                    ORDER BY v.fecha ASC
                ";
                break;

            case 'anio':
                // Últimos 12 meses agrupados por mes
                $sql = "
                    SELECT
                        DATE_FORMAT(v.fecha, '%m/%Y')         AS label,
                        COALESCE(SUM(vmp.monto), 0)           AS monto,
                        COUNT(DISTINCT v.id_venta)             AS cantidad
                    FROM ventas v
                    LEFT JOIN venta_medio_pago vmp ON vmp.id_venta = v.id_venta
                    WHERE v.fecha >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                    GROUP BY DATE_FORMAT(v.fecha, '%Y-%m')
                    ORDER BY DATE_FORMAT(v.fecha, '%Y-%m') ASC
                ";
                break;

            default: // 'mes' — días del mes actual
                $sql = "
                    SELECT
                        DATE_FORMAT(v.fecha, '%d/%m') AS label,
                        COALESCE(SUM(vmp.monto), 0)   AS monto,
                        COUNT(DISTINCT v.id_venta)     AS cantidad
                    FROM ventas v
                    LEFT JOIN venta_medio_pago vmp ON vmp.id_venta = v.id_venta
                    WHERE YEAR(v.fecha)  = YEAR(CURDATE())
                      AND MONTH(v.fecha) = MONTH(CURDATE())
                    GROUP BY v.fecha
                    ORDER BY v.fecha ASC
                ";
                break;
        }

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels     = [];
        $montos     = [];
        $cantidades = [];

        foreach ($rows as $row) {
            $labels[]     = $row['label'];
            $montos[]     = (float) $row['monto'];
            $cantidades[] = (int)   $row['cantidad'];
        }

        return [
            'labels'     => $labels,
            'montos'     => $montos,
            'cantidades' => $cantidades,
        ];
    }
    /**
     * Retorna el monto total cobrado por cada medio de pago según el período.
     * Siempre incluye los 6 medios, con 0.00 si no hay datos en el período.
     *
     * @param string $periodo  'dia' | 'semana' | 'mes' | 'anio'
     * @return array<string, float>  ['Efectivo' => 1500.00, ...]
     */
    public function getMediosPagoData(string $periodo): array
    {
        // Inicializar todos los medios en 0 para garantizar presencia aunque no haya datos
        $result = [
            'Efectivo'          => 0.00,
            'Transferencia'     => 0.00,
            'Mercado_pago'      => 0.00,
            'Cheque'            => 0.00,
            'E-cheque'          => 0.00,
            'Cuenta Corriente'  => 0.00,
        ];

        switch ($periodo) {
            case 'dia':
                $where = 'v.fecha = CURDATE()';
                break;
            case 'semana':
                $where = 'v.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)';
                break;
            case 'anio':
                $where = 'v.fecha >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)';
                break;
            default: // 'mes'
                $where = 'YEAR(v.fecha) = YEAR(CURDATE()) AND MONTH(v.fecha) = MONTH(CURDATE())';
                break;
        }

        $sql = "
            SELECT
                mp.medio_pago,
                COALESCE(SUM(vmp.monto), 0) AS total
            FROM ventas v
            INNER JOIN venta_medio_pago vmp ON vmp.id_venta = v.id_venta
            INNER JOIN medio_pago mp ON mp.id_medio_pago = vmp.id_medio_pago
            WHERE {$where}
            GROUP BY mp.medio_pago
        ";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            if (array_key_exists($row['medio_pago'], $result)) {
                $result[$row['medio_pago']] = (float) $row['total'];
            }
        }

        return $result;
    }
}
