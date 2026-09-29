<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo DetalleCtaCte
 */
class DetalleCtaCte
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los detalles (movimientos) de una cuenta corriente.
     */
    public function getByCtaCte(int $idCtaCte): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM detalle_cta_cte_cliente
             WHERE id_cta_cte_cliente = :id_cta_cte
             ORDER BY fecha ASC, id_detalle_cta_cte_cliente ASC'
        );
        $stmt->execute([':id_cta_cte' => $idCtaCte]);
        return $stmt->fetchAll();
    }
}
