<?php
/**
 * Database Configuration for Free Hosting
 * Supports InfinityFree, 000webhost, and other free PHP hosting
 */

class Database {
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $port;
    private $pdo;

    public function __construct() {
        $this->setCredentials();
        $this->connect();
    }

    private function setCredentials() {
        // Auto-detect hosting environment
        if ($this->isInfinityFree()) {
            // InfinityFree configuration for account if0_39899884
            $this->host = 'sql200.infinityfree.com'; // Will be provided by InfinityFree
            $this->port = 3306;
            $this->dbname = $_ENV['DB_NAME'] ?? 'if0_39899884_productcatalog'; // Your database name
            $this->username = $_ENV['DB_USER'] ?? 'if0_39899884'; // Your username
            $this->password = $_ENV['DB_PASS'] ?? 'your_password'; // Replace with actual password
        } elseif ($this->is000webhost()) {
            // 000webhost configuration
            $this->host = 'localhost';
            $this->port = 3306;
            $this->dbname = $_ENV['DB_NAME'] ?? 'id12345_productcatalog'; // Replace with your DB name
            $this->username = $_ENV['DB_USER'] ?? 'id12345_username'; // Replace with your username
            $this->password = $_ENV['DB_PASS'] ?? 'your_password'; // Replace with your password
        } else {
            // Local development
            $this->host = 'localhost';
            $this->port = 3306;
            $this->dbname = 'product_catalog';
            $this->username = 'root';
            $this->password = '';
        }
    }

    private function isInfinityFree() {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        return strpos($host, '.infinityfreeapp.com') !== false ||
               strpos($host, '.000.pe') !== false ||
               strpos($host, '.rf.gd') !== false ||
               strpos($host, '.great-site.net') !== false ||
               strpos($host, '.somee.com') !== false ||
               strpos($host, 'kamran.gamer.gd') !== false ||
               strpos($host, 'if0_39899884') !== false;
    }

    private function is000webhost() {
        return strpos($_SERVER['HTTP_HOST'] ?? '', '.000webhostapp.com') !== false;
    }

    private function connect() {
        try {
            if ($this->isLocalhost()) {
                // Local development with socket
                $dsn = "mysql:host={$this->host};port={$this->port};unix_socket=/tmp/mysql.sock;dbname={$this->dbname};charset=utf8mb4";
            } else {
                // Production: Standard TCP connection
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30
            ];

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log error and show user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }

    private function isLocalhost() {
        return in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']);
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function createDatabase() {
        // Skip database creation on free hosting (databases are pre-created)
        if (!$this->isLocalhost()) {
            return true;
        }
        
        try {
            // Local development only
            $dsn = "mysql:host={$this->host};port={$this->port};unix_socket=/tmp/mysql.sock;charset=utf8mb4";
            $tempPdo = new PDO($dsn, $this->username, $this->password);
            $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return true;
        } catch (PDOException $e) {
            error_log("Database creation failed: " . $e->getMessage());
            return false;
        }
    }

    public function tableExists($tableName) {
        try {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Table check failed: " . $e->getMessage());
            return false;
        }
    }
}