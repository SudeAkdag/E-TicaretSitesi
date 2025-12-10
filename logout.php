<?php
// logout.php
include 'db_config.php'; // Session'ı kullanmak için dahil edilmeli

// Tüm session değişkenlerini sıfırla
$_SESSION = array();

// Session çerezini sil (Güvenlik için önerilir)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session'ı tamamen yok et
session_destroy();

// Kullanıcıyı giriş sayfasına yönlendir
header("location: login.php");
exit;
?>