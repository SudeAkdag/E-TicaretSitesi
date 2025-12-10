<?php
// login.php
include 'db_config.php';

$hata_mesaji = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $sifre = $_POST['sifre'];

    // Saklı Yordamı çağırma (SP_KullaniciGirisKontrol)
    if ($stmt = $conn->prepare("CALL SP_KullaniciGirisKontrol(?)")) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        $conn->next_result(); // MySQLi bugfix

        if ($user) {
            // ŞİFRE KONTROLÜ
            if ($sifre == '123') { // Örnek olarak tüm kullanıcıların şifresini '123' kabul edelim.
                
                // SESSION Değişkenleri Saklama
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['kullanici_id'] = $user['KullaniciID'];
                $_SESSION['rol_id'] = $user['RolID'];
                $_SESSION['email'] = $email;

                // Rol Yönlendirmesi
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
                        $hata_mesaji = "Bilinmeyen kullanıcı rolü.";
                        break;
                }
                exit;

            } else {
                $hata_mesaji = "Hatalı şifre.";
            }
        } else {
            $hata_mesaji = "Kullanıcı bulunamadı veya pasif durumda.";
        }
    } else {
        $hata_mesaji = "Veritabanı sorgu hatası: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>E-Ticaret Sistemi Giriş</title>
</head>
<body>
    <h2>E-Ticaret ve Stok Takip Sistemi Girişi</h2>
    <?php if ($hata_mesaji) echo "<p style='color:red;'>$hata_mesaji</p>"; ?>
    <form action="login.php" method="post">
        <label for="email">E-posta:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="sifre">Şifre:</label><br>
        <input type="password" id="sifre" name="sifre" required><br><br>
        <input type="submit" value="Giriş Yap">
    </form>
    <p>Test Hesapları:</p>
    <ul>
        <li>Yönetici: yonetici_ege@sirket.com (şifre: 123)</li>
        <li>Personel: depo_ali@sirket.com (şifre: 123)</li>
        <li>Müşteri: mustafa@mail.com (şifre: 123)</li>
    </ul>
</body>
</html>