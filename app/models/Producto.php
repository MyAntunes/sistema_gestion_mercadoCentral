<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo Producto
 */
class Producto
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los productos con datos del proveedor, ordenados por nombre.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT p.*, pr.razon_social
             FROM producto p
             JOIN proveedor pr ON p.id_proveedor = pr.id_proveedor
             ORDER BY p.nombre ASC'
        );
        return $stmt->fetchAll();
    }

    /**
     * Alias de getAll() — retorna productos con JOIN a proveedor.
     */
    public function getAllWithProveedor(): array
    {
        return $this->getAll();
    }

    /**
     * Retorna un producto por ID (con JOIN a proveedor) o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, pr.razon_social
             FROM producto p
             JOIN proveedor pr ON p.id_proveedor = pr.id_proveedor
             WHERE p.id_producto = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Busca productos cuyo nombre o especie contengan la cadena dada.
     */
    public function search(string $q): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, pr.razon_social
             FROM producto p
             JOIN proveedor pr ON p.id_proveedor = pr.id_proveedor
             WHERE p.nombre LIKE :q OR p.especie LIKE :q
             ORDER BY p.nombre ASC'
        );
        $stmt->execute([':q' => '%' . $q . '%']);
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo producto.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO producto (nombre, especie, precio_costo, precio_venta, id_proveedor)
             VALUES (:nombre, :especie, :precio_costo, :precio_venta, :id_proveedor)'
        );

        return $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':especie'       => $data['especie']       ?? null,
            ':precio_costo'  => $data['precio_costo'],
            ':precio_venta'  => $data['precio_venta'],
            ':id_proveedor'  => $data['id_proveedor'],
        ]);
    }

    /**
     * Actualiza los datos de un producto.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE producto
             SET nombre        = :nombre,
                 especie       = :especie,
                 precio_costo  = :precio_costo,
                 precio_venta  = :precio_venta,
                 id_proveedor  = :id_proveedor
             WHERE id_producto = :id'
        );

        return $stmt->execute([
            ':nombre'        => $data['nombre'],
            ':especie'       => $data['especie']       ?? null,
            ':precio_costo'  => $data['precio_costo'],
            ':precio_venta'  => $data['precio_venta'],
            ':id_proveedor'  => $data['id_proveedor'],
            ':id'            => $id,
        ]);
    }

    /**
     * Elimina un producto si no tiene ventas asociadas.
     * Retorna false si tiene ventas.
     */
    public function delete(int $id): bool
    {
        if ($this->hasVentas($id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM producto WHERE id_producto = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica si un producto tiene registros en detalle_ventas.
     */
    public function hasVentas(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM detalle_ventas WHERE id_producto = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
