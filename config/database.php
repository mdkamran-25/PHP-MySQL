<?php
/**
 * Database Configuration
 * Secure database connection with error handling
 * Production-ready for DigitalOcean deployment
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
        // Check for DigitalOcean DATABASE_URL environment variable
        if (isset($_ENV['DATABASE_URL']) && !empty($_ENV['DATABASE_URL'])) {
            $dbConfig = $this->parseUrl($_ENV['DATABASE_URL']);
            $this->host = $dbConfig['host'];
            $this->port = $dbConfig['port'] ?? 25060;
            $this->dbname = $dbConfig['database'];
            $this->username = $dbConfig['user'];
            $this->password = $dbConfig['password'];
        } else {
            // Fallback to local development settings
            $this->host = 'localhost';
            $this->port = 3306;
            $this->dbname = 'product_catalog';
            $this->username = 'root';
            $this->password = '';
        }
    }

    private function parseUrl($url) {
        $parsed = parse_url($url);
        return [
            'host' => $parsed['host'],
            'port' => $parsed['port'] ?? 25060,
            'database' => ltrim($parsed['path'], '/'),
            'user' => $parsed['user'],
            'password' => $parsed['pass']
        ];
    }

    private function connect() {
        try {
            // Build DSN based on environment
            if (isset($_ENV['DATABASE_URL'])) {
                // Production: DigitalOcean managed database
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
                ];
            } else {
                // Development: Local MySQL
                $dsn = "mysql:host={$this->host};port={$this->port};unix_socket=/tmp/mysql.sock;dbname={$this->dbname};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
            }

            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log error and show user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function createDatabase() {
        try {
            // Connect without database name to create it
            if (isset($_ENV['DATABASE_URL'])) {
                // Production: Database already exists in managed service
                return true;
            }
            
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
?>