<?php
// db_config.php

// Oturumu başlatma (Session kullanımı için Gerekli)
session_start();

$host = "localhost"; // Sadece host adı
$port = 3307;        // Port numarasını ayrı değişkene alın
$username = "root"; 
$password = "";      
$dbname = "eticaret_proje";

// MySQLi ile Bağlantı Kurma
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Bağlantı Kontrolü
if ($conn->connect_error) {
    // Bağlantı başarısız olursa projeyi durdur
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Türkçe karakter desteği için set_charset
$conn->set_charset("utf8");
?>