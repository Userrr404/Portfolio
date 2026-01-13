<?php
namespace app\core;

use PDO;
use PDOException;
use Throwable;

class DB
{
    private static ?DB $instance = null;
    private ?PDO $pdo = null;

    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

        } catch (PDOException $e) {
            app_log("DB CONNECTION FAILED: " . $e->getMessage(), "error");
            // throw new \Exception("Database connection failed.");
            $this->pdo = null;
        }
    }

    public static function getInstance(): DB
    {
        return self::$instance ??= new DB();
    }

    public function pdo(): ?PDO
    {
        if ($this->pdo === null) {
            return null;
        }

        try {
            $this->pdo->query("SELECT 1");
        } catch (Throwable $e) {
            app_log("DB LOST CONNECTION: " . $e->getMessage(), "warning");
            // self::$instance = new DB();
            $this->pdo = null;
            return null;
        }

        return $this->pdo;
    }
}
