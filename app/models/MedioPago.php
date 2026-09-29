<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo MedioPago
 */
class MedioPago
{
    private PDO $db;

    /** Valores válidos del ENUM medio_pago en la base de datos */
    private const ENUM_VALUES = [
        'Efectivo',
        'E-cheque',
        'Cheque',
        'Transferencia',
        'Mercado_pago',
        'Cuenta Corriente',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna los valores válidos del ENUM.
     */
    public function getEnumValues(): array
    {
        return self::ENUM_VALUES;
    }

    /**
     * Retorna todos los medios de pago, ordenados por ID.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM medio_pago ORDER BY id_medio_pago ASC');
        return $stmt->fetchAll();
    }

    /**
     * Retorna un medio de pago por ID o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM medio_pago WHERE id_medio_pago = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Crea un nuevo medio de pago.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO medio_pago (medio_pago) VALUES (:medio_pago)');

        return $stmt->execute([':medio_pago' => $data['medio_pago']]);
    }

    /**
     * Actualiza el medio de pago.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE medio_pago SET medio_pago = :medio_pago WHERE id_medio_pago = :id'
        );

        return $stmt->execute([
            ':medio_pago' => $data['medio_pago'],
            ':id'         => $id,
        ]);
    }

    /**
     * Elimina un medio de pago si no tiene ventas asociadas.
     * Retorna false si tiene ventas.
     */
    public function delete(int $id): bool
    {
        if ($this->hasVentas($id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM medio_pago WHERE id_medio_pago = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica si un medio de pago tiene registros en venta_medio_pago.
     */
    public function hasVentas(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM venta_medio_pago WHERE id_medio_pago = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
