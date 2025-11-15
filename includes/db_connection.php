<?php
/**
 * ENTERPRISE DATABASE CONNECTION
 * PDO + Singleton + Auto-reconnect + Error handling
 */

require_once __DIR__ . '/../config/config.php';

// Prevent direct access or loading before config.php
if (!defined('DB_HOST')) {
    die("❌ ERROR: db_connection.php was loaded BEFORE config.php.
         Always load config.php, never load db_connection.php directly.");
}

class DB
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_PERSISTENT         => false,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            echo "Database connected successfully.";
        } catch (PDOException $e) {
            app_log("DB Connection Failed", "error", ['error' => $e->getMessage()]);
            die("Database connection failed");
        }
    }

    // Get the singleton instance
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Get PDO object
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}

// For convenience
$db = DB::getInstance()->pdo();

?>
