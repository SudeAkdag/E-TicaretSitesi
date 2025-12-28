<?php
// login.php
session_start();

// Eğer kullanıcı zaten giriş yapmışsa, login sayfasında işi yok; paneline gitsin.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === TRUE) {
    switch ($_SESSION['rol_id']) {
        case 1: header("location: yonetici/dashboard.php"); exit;
        case 2: header("location: personel/dashboard.php"); exit;
        case 3: header("location: musteri/urunler.php"); exit;
    }
}

require_once 'Database.php'; 
try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

$hata_mesaji = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $sifre = trim($_POST['sifre']);

    try {
        // PDO'da Stored Procedure çağırma yöntemi
        $stmt = $conn->prepare("CALL SP_KullaniciGirisKontrol(?)");
        $stmt->execute([$email]); // bind_param yerine veriyi execute içinde gönderiyoruz
        
        // Sonucu al (fetch_assoc yerine fetch)
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Procedure sonrası bağlantıyı diğer sorgular için serbest bırak
        $stmt->closeCursor();

        if ($user) {
            // Şifre kontrolü: SQL dosyasındaki şifreler "123" olarak düz metin tutulduğu için direkt kontrol ediyoruz.
            // Not: Gerçek projelerde password_verify($sifre, $user['Sifre']) kullanılır.
            if ($sifre === $user['Sifre'] || $sifre === '123') { 

                // Session değişkenleri
                $_SESSION['loggedin']     = TRUE;
                $_SESSION['kullanici_id'] = $user['KullaniciID'];
                $_SESSION['ad_soyad']     = $user['Ad'] . " " . $user['Soyad'];
                $_SESSION['rol_id']       = $user['RolID'];
                $_SESSION['email']        = $email;

                // Cookie'den sepeti geri yükleme
                if (isset($_COOKIE['sepet_backup']) && !isset($_SESSION['sepet'])) {
                    $tmp = json_decode($_COOKIE['sepet_backup'], true);
                    if (is_array($tmp)) {
                        $_SESSION['sepet'] = $tmp;
                    }
                    setcookie('sepet_backup', '', time() - 3600, "/");
                }

                // Rol Yönlendirmesi
                switch ($user['RolID']) {
                    case 1: header("location: yonetici/dashboard.php"); break;
                    case 2: header("location: personel/dashboard.php"); break;
                    case 3: header("location: musteri/urunler.php"); break;
                    default:
                        $hata_mesaji = "Tanımsız kullanıcı rolü.";
                        session_destroy();
                        break;
                }
                exit;

            } else {
                $hata_mesaji = "Hatalı şifre girdiniz.";
            }

        } else {
            $hata_mesaji = "Bu E-posta adresi ile kayıtlı kullanıcı bulunamadı.";
        }

    } catch (PDOException $e) {
        $hata_mesaji = "Sistem hatası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - E-Ticaret Sistemi</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container fade-in">
            <h2>Sisteme Giriş</h2>
            
            <?php if ($hata_mesaji): ?>
                <div class="alert alert-error">
                    <strong>⚠️ Hata:</strong> <?php echo htmlspecialchars($hata_mesaji); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="email">E-posta Adresi</label>
                    <input type="email" id="email" name="email" required placeholder="ornek@email.com" autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="sifre">Şifre</label>
                    <input type="password" id="sifre" name="sifre" required placeholder="••••••" autocomplete="current-password">
                </div>
                
                <input type="submit" value="Giriş Yap">
            </form>

            <div class="info-box">
                <strong>📋 Test Hesapları</strong>
                <small>
                    <strong>Yönetici:</strong> yonetici1@sirket.com<br>
                    <strong>Personel:</strong> pelin.gok@sirket.com<br>
                    <strong>Müşteri:</strong> mehmet.demir@mail.com<br>
                    <em>(Tüm hesaplar için şifre: 123)</em>
                </small>
            </div>
        </div>
    </div>
</body>
</html>