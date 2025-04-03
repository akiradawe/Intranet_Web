<?php
class Database {
    private $host = '127.0.0.1';
    private $db_name = 'intranet_db';
    private $username = 'intranet_user';
    private $password = 'tl((C@8r7Kp[Y_Yi';
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            $this->conn->set_charset("utf8");
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
        } catch(Exception $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

// Create database connection
$database = new Database();
$db = $database->getConnection();
?> 