<?php
/**
 * Database — Singleton PDO
 *
 * Uso:
 *   $db = Database::getInstance()->getConnection();
 */
class Database
{
    /** @var Database|null */
    private static ?Database $instance = null;

    /** @var PDO */
    private PDO $pdo;

    /**
     * Constructor privado: establece la conexión PDO.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    /**
     * Retorna la única instancia de Database.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retorna la conexión PDO.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    // Impedir clonación y deserialización
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new \RuntimeException('Cannot unserialize a singleton.');
    }
}
