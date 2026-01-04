<?php
/**
 * Database Connection Handler
 * PDO-based database connection with error handling and prepared statements
 */

require_once __DIR__ . '/db-config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        try {
            $host = EnvLoader::get('DB_HOST', 'localhost');
            $dbname = EnvLoader::get('DB_NAME', 'worldtrust_atm');
            $username = EnvLoader::get('DB_USER', 'root');
            $password = EnvLoader::get('DB_PASSWORD', '');
            
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true, // Connection pooling
            ];
            
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Log error without exposing sensitive information
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check configuration.");
        }
    }
    
    /**
     * Get database instance (singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement with parameters
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception("Database operation failed.");
        }
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Get database connection
 */
function getDB() {
    return Database::getInstance();
}
