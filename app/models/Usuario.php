<?php
require_once __DIR__ . '/Database.php';

/**
 * Modelo Usuario
 */
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Busca un usuario por nombre_usuario o email.
     */
    public function findByUsernameOrEmail(string $input): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuario WHERE nombre_usuario = :input_user OR email = :input_email LIMIT 1'
        );
        $stmt->execute([
            ':input_user'  => $input,
            ':input_email' => $input,
        ]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Retorna todos los usuarios ordenados por id.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM usuario ORDER BY id_usuario ASC');
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un usuario por ID.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuario WHERE id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Crea un nuevo usuario hasheando la contraseña con PASSWORD_BCRYPT.
     */
    public function create(array $data): bool
    {
        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
        $estado = $data['estado'] ?? 'Activo';

        $stmt = $this->db->prepare(
            'INSERT INTO usuario (nombre_usuario, email, password_hash, rol, estado)
             VALUES (:nombre_usuario, :email, :password_hash, :rol, :estado)'
        );

        return $stmt->execute([
            ':nombre_usuario' => $data['nombre_usuario'],
            ':email'          => $data['email'],
            ':password_hash'  => $passwordHash,
            ':rol'            => $data['rol'],
            ':estado'         => $estado,
        ]);
    }

    /**
     * Actualiza los datos de un usuario (sin modificar password).
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuario
             SET nombre_usuario = :nombre_usuario,
                 email = :email,
                 rol = :rol,
                 estado = :estado
             WHERE id_usuario = :id'
        );

        return $stmt->execute([
            ':nombre_usuario' => $data['nombre_usuario'],
            ':email'          => $data['email'],
            ':rol'            => $data['rol'],
            ':estado'         => $data['estado'] ?? 'Activo',
            ':id'             => $id,
        ]);
    }

    /**
     * Actualiza solo el hash del password.
     */
    public function updatePassword(int $id, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            'UPDATE usuario SET password_hash = :password_hash WHERE id_usuario = :id'
        );

        return $stmt->execute([
            ':password_hash' => $passwordHash,
            ':id'            => $id,
        ]);
    }

    /**
     * Alterna el estado del usuario entre Activo e Inactivo.
     */
    public function toggleEstado(int $id): bool
    {
        $user = $this->getById($id);
        if (!$user) {
            return false;
        }

        $nuevoEstado = ($user['estado'] === 'Activo') ? 'Inactivo' : 'Activo';

        $stmt = $this->db->prepare('UPDATE usuario SET estado = :estado WHERE id_usuario = :id');
        return $stmt->execute([
            ':estado' => $nuevoEstado,
            ':id'     => $id,
        ]);
    }

    /**
     * Elimina un usuario por ID.
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM usuario WHERE id_usuario = :id');
        return $stmt->execute([':id' => $id]);
    }
}
