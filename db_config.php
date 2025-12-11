<?php
// db_config.php

// Oturum Kontrolü:
// Diğer dosyalarda (login.php vb.) session_start() zaten kullanıldığı için,
// burada çakışma olmasın diye "Oturum açık değilse aç" kontrolü ekliyoruz.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı Bilgileri
$host = "localhost";
$port = 3307;        // MySQL portunuz. (XAMPP'te genelde 3306'dır, siz 3307 kullanıyorsanız böyle kalsın)
$username = "root";  // Varsayılan kullanıcı
$password = "";      // Varsayılan şifre (boş)
$dbname = "eticaret_proje"; // Veritabanı adınız

// a) Madde: MySQLi ile Bağlantı Kurma
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Bağlantı Kontrolü
if ($conn->connect_error) {
    // Bağlantı başarısız olursa çalışmayı durdur
    die("Veritabanı bağlantı hatası: " . $conn->connect_error);
}

// Türkçe karakter sorunu yaşamamak için UTF-8 ayarı
$conn->set_charset("utf8");

?>