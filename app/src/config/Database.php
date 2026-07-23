<?php
/**
 * Konfiguracja bazy danych
 * Połączenie PDO do MySQL
 */

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $host = getenv('MYSQL_HOST') ?: 'mysql';
            $db = getenv('MYSQL_DB') ?: 'orzeszekstudies';
            $user = getenv('MYSQL_USER') ?: 'user';
            $pass = getenv('MYSQL_PASSWORD') ?: 'password';

            $this->pdo = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            
            // Ustaw charset na UTF-8
            $this->pdo->exec("SET CHARACTER SET utf8mb4");
            $this->pdo->exec("SET NAMES utf8mb4");
            $this->pdo->exec("SET CHARACTER_SET_CLIENT=utf8mb4");
            $this->pdo->exec("SET CHARACTER_SET_RESULTS=utf8mb4");
            $this->pdo->exec("SET COLLATION_CONNECTION=utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            die("Błąd połączenia z bazą danych: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function __clone() {}
    public function __wakeup() {}
}
?>
