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
    <title>Giriş Yap - E-Ticaret Sistemi</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f0f2f5; }
        .login-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); width: 300px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; box-sizing: border-box; }
        input[type="submit"] { width: 100%; padding: 10px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        input[type="submit"]:hover { background-color: #0056b3; }
        .error { color: red; font-size: 0.9em; margin-bottom: 10px; }
        .info { font-size: 0.8em; color: #666; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align:center;">Sisteme Giriş</h2>
        
        <?php if ($hata_mesaji): ?>
            <div class="error"><?php echo $hata_mesaji; ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <label for="email">E-posta:</label>
            <input type="email" id="email" name="email" required placeholder="ornek@email.com">
            
            <label for="sifre">Şifre:</label>
            <input type="password" id="sifre" name="sifre" required placeholder="******">
            
            <input type="submit" value="Giriş Yap">
        </form>

        <div class="info">
            <strong>Test Hesapları:</strong><br>
            Admin: yonetici1@sirket.com<br>
            Personel: pelin.gok@sirket.com<br>
            Müşteri: mehmet.demir@mail.com<br>
            (Şifreler: 123)
        </div>
    </div>
</body>
</html>