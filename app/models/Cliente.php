<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo Cliente
 */
class Cliente
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Retorna todos los clientes ordenados por nombre_apellido.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM cliente ORDER BY nombre_apellido ASC');
        return $stmt->fetchAll();
    }

    /**
     * Retorna un cliente por ID o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM cliente WHERE id_cliente = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    /**
     * Busca clientes cuyo nombre_apellido contenga la cadena dada.
     */
    public function search(string $q): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM cliente WHERE nombre_apellido LIKE :q ORDER BY nombre_apellido ASC'
        );
        $stmt->execute([':q' => '%' . $q . '%']);
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo cliente.
     */
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO cliente (nombre_apellido, domicilio, localidad, codigo_postal, telefono_1, telefono_2, cuil)
             VALUES (:nombre_apellido, :domicilio, :localidad, :codigo_postal, :telefono_1, :telefono_2, :cuil)'
        );

        return $stmt->execute([
            ':nombre_apellido' => $data['nombre_apellido'],
            ':domicilio'       => $data['domicilio']       ?? null,
            ':localidad'       => $data['localidad']       ?? null,
            ':codigo_postal'   => $data['codigo_postal']   ?? null,
            ':telefono_1'      => $data['telefono_1'],
            ':telefono_2'      => $data['telefono_2'],
            ':cuil'            => $data['cuil']            ?? null,
        ]);
    }

    /**
     * Actualiza los datos de un cliente.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE cliente
             SET nombre_apellido = :nombre_apellido,
                 domicilio       = :domicilio,
                 localidad       = :localidad,
                 codigo_postal   = :codigo_postal,
                 telefono_1      = :telefono_1,
                 telefono_2      = :telefono_2,
                 cuil            = :cuil
             WHERE id_cliente = :id'
        );

        return $stmt->execute([
            ':nombre_apellido' => $data['nombre_apellido'],
            ':domicilio'       => $data['domicilio']       ?? null,
            ':localidad'       => $data['localidad']       ?? null,
            ':codigo_postal'   => $data['codigo_postal']   ?? null,
            ':telefono_1'      => $data['telefono_1'],
            ':telefono_2'      => $data['telefono_2'],
            ':cuil'            => $data['cuil']            ?? null,
            ':id'              => $id,
        ]);
    }

    /**
     * Elimina un cliente si no tiene ventas asociadas.
     * Retorna false si tiene ventas.
     */
    public function delete(int $id): bool
    {
        if ($this->hasVentas($id)) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM cliente WHERE id_cliente = :id');
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Verifica si un cliente tiene ventas asociadas.
     */
    public function hasVentas(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ventas WHERE id_cliente = :id');
        $stmt->execute([':id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
