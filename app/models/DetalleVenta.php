<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo DetalleVenta
 */
class DetalleVenta
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los detalles de una venta con información del producto.
     */
    public function getByVenta(int $idVenta): array
    {
        $stmt = $this->db->prepare(
            'SELECT dv.*, p.nombre AS producto_nombre, p.especie AS producto_especie
             FROM detalle_ventas dv
             JOIN producto p ON dv.id_producto = p.id_producto
             WHERE dv.id_venta = :id_venta
             ORDER BY dv.id_detalle_ventas ASC'
        );
        $stmt->execute([':id_venta' => $idVenta]);
        return $stmt->fetchAll();
    }
}
