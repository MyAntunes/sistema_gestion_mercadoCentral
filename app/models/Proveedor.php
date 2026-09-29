<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo Proveedor
 */
class Proveedor
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los proveedores ordenados por razon_social.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM proveedor ORDER BY razon_social ASC');
        return $stmt->fetchAll();
    }

    /**
     * Retorna un proveedor por ID o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM proveedor WHERE id_proveedor = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Busca proveedores cuya razon_social contenga la cadena dada.
     */
    public function search(string $q): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM proveedor WHERE razon_social LIKE :q ORDER BY razon_social ASC'
        );
        $stmt->execute([':q' => '%' . $q . '%']);
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo proveedor.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO proveedor (razon_social, direccion, telefono_1, telefono_2, cuit)
             VALUES (:razon_social, :direccion, :telefono_1, :telefono_2, :cuit)'
        );

        return $stmt->execute([
            ':razon_social' => $data['razon_social'],
            ':direccion'    => $data['direccion']   ?? null,
            ':telefono_1'   => $data['telefono_1'],
            ':telefono_2'   => $data['telefono_2'],
            ':cuit'         => $data['cuit']        ?? null,
        ]);
    }

    /**
     * Actualiza los datos de un proveedor.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE proveedor
             SET razon_social = :razon_social,
                 direccion    = :direccion,
                 telefono_1   = :telefono_1,
                 telefono_2   = :telefono_2,
                 cuit         = :cuit
             WHERE id_proveedor = :id'
        );

        return $stmt->execute([
            ':razon_social' => $data['razon_social'],
            ':direccion'    => $data['direccion']   ?? null,
            ':telefono_1'   => $data['telefono_1'],
            ':telefono_2'   => $data['telefono_2'],
            ':cuit'         => $data['cuit']        ?? null,
            ':id'           => $id,
        ]);
    }

    /**
     * Elimina un proveedor si no tiene productos asociados.
     * Retorna false si tiene productos.
     */
    public function delete(int $id): bool
    {
        if ($this->hasProductos($id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM proveedor WHERE id_proveedor = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica si un proveedor tiene productos asociados.
     */
    public function hasProductos(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM producto WHERE id_proveedor = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
