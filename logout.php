<?php
// logout.php
// b) Maddesi: Kullanıcı çıkış yapabilmelidir.

session_start(); // Oturumu silmek için önce başlatıp yakalamamız gerekir.

// 0. ADIM: Sepeti ayrı bir cookie'ye yedekle (varsa)
if (!empty($_SESSION['sepet'])) {
    setcookie(
        'sepet_backup',                 // cookie adı
        json_encode($_SESSION['sepet']),// içerik
        time() + 7*24*60*60,            // 7 gün geçerli
        "/"                             // tüm sitede geçerli
    );
}

// 1. Tüm session değişkenlerini hafızadan sil
$_SESSION = array();

// 2. Tarayıcıdaki Session Çerezini (Cookie) sil (Tam güvenlik için)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Sunucudaki oturumu tamamen yok et
session_destroy();

// 4. Kullanıcıyı giriş sayfasına geri gönder
header("location: login.php");
exit;
?>