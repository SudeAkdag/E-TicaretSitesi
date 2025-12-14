<?php
// login.php
// 5. Madde: Session başlatıyoruz ki bilgiler sayfalar arası taşınsın.
session_start();

// Eğer kullanıcı zaten giriş yapmışsa, login sayfasında işi yok; paneline gitsin.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === TRUE) {
    switch ($_SESSION['rol_id']) {
        case 1: header("location: yonetici/dashboard.php"); exit;
        case 2: header("location: personel/dashboard.php"); exit;
        case 3: header("location: musteri/dashboard.php"); exit;
    }
}

include 'db_config.php';

$hata_mesaji = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Formdan gelen verileri temizleyelim (Basit güvenlik)
    $email = trim($_POST['email']);
    $sifre = trim($_POST['sifre']);

    // 7. Madde: SQL sorgusu Saklı Yordam (Stored Procedure) ile çağrılıyor.
    if ($stmt = $conn->prepare("CALL SP_KullaniciGirisKontrol(?)")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result(); // MySQLi bugfix: SP sonrası bağlantıyı temizle

        if ($user) {
            // ŞİFRE KONTROLÜ
            // Not: Gerçek projede password_verify() kullanılır, burada '123' sabit kabul edildi.
            if ($sifre == '123') { 
                
                // 5. Madde: Session değişkenlerini saklıyoruz.
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['kullanici_id'] = $user['KullaniciID'];
                $_SESSION['ad_soyad'] = $user['Ad'] . " " . $user['Soyad']; // Hoşgeldin mesajı için ekledim
                $_SESSION['rol_id'] = $user['RolID'];
                $_SESSION['email'] = $email;

                // c) Madde: Rol Yönlendirmesi (Her rol kendi sayfasına gider)
                switch ($user['RolID']) {
                    case 1: // Yönetici
                        header("location: yonetici/dashboard.php");
                        break;
                    case 2: // Personel/Depo Sorumlusu
                        header("location: personel/dashboard.php");
                        break;
                    case 3: // Müşteri
                        header("location: musteri/dashboard.php");
                        break;
                    default:
                        $hata_mesaji = "Tanımsız kullanıcı rolü.";
                        // Hata durumunda session'ı temizle
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
    } else {
        $hata_mesaji = "Sistem hatası: " . $conn->error;
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