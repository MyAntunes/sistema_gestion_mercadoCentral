<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo VentaMedioPago
 */
class VentaMedioPago
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los medios de pago asociados a una venta.
     */
    public function getByVenta(int $idVenta): array
    {
        $stmt = $this->db->prepare(
            'SELECT vmp.*, mp.medio_pago AS medio_pago_nombre
             FROM venta_medio_pago vmp
             JOIN medio_pago mp ON vmp.id_medio_pago = mp.id_medio_pago
             WHERE vmp.id_venta = :id_venta
             ORDER BY vmp.id_venta_medio_pago ASC'
        );
        $stmt->execute([':id_venta' => $idVenta]);
        return $stmt->fetchAll();
    }
}
