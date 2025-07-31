<?php
class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private string $charset;

    private function __construct() {
        $this->host = DB_HOST;
        $this->dbname = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
        
        $this->connect();
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            throw new Exception("Lỗi kết nối database: Không thể kết nối đến máy chủ cơ sở dữ liệu");
        }
    }

    public function getConnection(): PDO {
        if ($this->connection === null) {
            $this->connect();
        }
        
        if ($this->connection === null) {
            throw new Exception("Database connection is not established");
        }
        
        return $this->connection;
    }

    public function select($query, $params = []): array {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn SELECT: " . $e->getMessage());
        }
    }

    public function selectOne($query, $params = []): array|false {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn SELECT ONE: " . $e->getMessage());
        }
    }

    public function insert($query, $params = []): string|false {
        try {
            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                return $this->connection->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn INSERT: " . $e->getMessage());
        }
    }

    public function update($query, $params = []): int {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn UPDATE: " . $e->getMessage());
        }
    }

    public function delete($query, $params = []): int {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Lỗi truy vấn DELETE: " . $e->getMessage());
        }
    }

    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    public function commit(): bool {
        return $this->connection->commit();
    }

    public function rollback(): bool {
        return $this->connection->rollback();
    }

    public function testConnection(): bool {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function closeConnection(): void {
        $this->connection = null;
    }

    private function __clone() {}

    public function __wakeup(): void {
        throw new Exception("Cannot unserialize a singleton.");
    }
}

function db(): Database {
    return Database::getInstance();
}

function getConnection(): PDO {
    return Database::getInstance()->getConnection();
}