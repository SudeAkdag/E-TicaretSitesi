<?php
class Database {
    private $host = "localhost";
    private $port = "3307"; 
    private $db_name = "eticaret_proje";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Önce veritabanı olmadan bağlan
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Veritabanı var mı bak, yoksa oluştur ve SQL'i yükle
            $query = $this->conn->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$this->db_name'");
            if ($query->fetchColumn() == 0) {
                $this->setupDatabase();
            }

            // Veritabanını seç
           $this->conn->exec("USE `$this->db_name`; SET NAMES utf8mb4;");
        } catch (PDOException $e) {
            die("Bağlantı Hatası: " . $e->getMessage());
        }
        return $this->conn;
    }

    private function setupDatabase() {
        $sql = file_get_contents(__DIR__ . '/veritabani.sql');
        // SQL içindeki DELIMITER'ları temizle (PHP anlamaz)
        $sql = preg_replace("/DELIMITER \/\/|DELIMITER ;/i", "", $sql);
        $sql = str_replace("//", ";", $sql);
        $this->conn->exec($sql);
    }
}