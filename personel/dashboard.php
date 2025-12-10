<?php
// /personel/dashboard.php

include '../db_config.php'; 

// Yetki Kontrolü: Rol ID'si 2 olmalı
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$mesaj = '';
$hata = '';

// A) Sipariş Durumu Güncelleme İşlemi (Form POST edildiğinde)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['siparis_guncelle'])) {
    $siparis_id = $_POST['siparis_id'];
    $yeni_durum = $_POST['yeni_durum'];

    // SP_SiparisDurumGuncelle Saklı Yordamını çağır (SIPARIS tablosunda UPDATE)
    if ($stmt = $conn->prepare("CALL SP_SiparisDurumGuncelle(?, ?)")) {
        $stmt->bind_param("is", $siparis_id, $yeni_durum);
        if ($stmt->execute()) {
            $mesaj = "Sipariş ID: $siparis_id durumu başarıyla **$yeni_durum** olarak güncellendi. (Trigger çalıştı!)";
        } else {
            $hata = "Durum güncelleme hatası: " . $stmt->error;
        }
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { ; } // Bugfix

    } else {
        $hata = "Saklı Yordam hazırlama hatası: " . $conn->error;
    }
}

// B) Bekleyen Siparişleri Listeleme (Sayfa yüklenirken)
// SP_BeklemedeOlanSiparisler Saklı Yordamı (SIPARIS, MUSTERI, KULLANICI, ADRES JOIN)
if ($stmt = $conn->prepare("CALL SP_BeklemedeOlanSiparisler()")) {
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $siparisler[] = $row;
        }
        $stmt->close();
    } else {
        $hata = "Sipariş listesi çekme hatası: " . $stmt->error;
    }

    while ($conn->more_results() && $conn->next_result()) { ; } // Bugfix
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Personel Paneli</title>
    <style> .status-beklemede { color: orange; font-weight: bold; } </style>
</head>
<body>
    <h1>📦 Personel Paneli</h1>
    <p>Hoş Geldiniz, Personel (<?php echo htmlspecialchars($_SESSION['email']); ?>)!</p>
    <p><a href="../logout.php">Çıkış Yap</a></p>
    
    <?php if ($mesaj): ?>
        <p style='color:green; font-weight: bold;'><?php echo $mesaj; ?></p>
    <?php endif; ?>
    <?php if ($hata): ?>
        <p style='color:red;'><?php echo $hata; ?></p>
    <?php endif; ?>

    <h2>Bekleyen Siparişler Listesi</h2>
    
    <?php if (empty($siparisler)): ?>
        <p>Şu anda bekleyen sipariş bulunmamaktadır.</p>
    <?php else: ?>
        <table border="1" style="width: 100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tarih</th>
                    <th>Toplam Tutar</th>
                    <th>Müşteri</th>
                    <th>Teslimat Adresi</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siparisler as $siparis): ?>
                <tr>
                    <td><?php echo htmlspecialchars($siparis['SiparisID']); ?></td>
                    <td><?php echo htmlspecialchars($siparis['SiparisTarihi']); ?></td>
                    <td><?php echo number_format($siparis['ToplamTutar'], 2); ?> TL</td>
                    <td><?php echo htmlspecialchars($siparis['MusteriAd']) . ' ' . htmlspecialchars($siparis['MusteriSoyad']); ?></td>
                    <td><?php echo htmlspecialchars(substr($siparis['TeslimatAdresi'], 0, 50)) . '...'; ?></td>
                    <td class="status-beklemede"><?php echo htmlspecialchars($siparis['Durum']); ?></td>
                    <td>
                        <form method="POST" action="dashboard.php" style="display:inline;">
                            <input type="hidden" name="siparis_id" value="<?php echo $siparis['SiparisID']; ?>">
                            <select name="yeni_durum" required>
                                <option value="Hazirlaniyor">Hazırlanıyor</option>
                                <option value="Kargoda">Kargoda</option>
                                <option value="Teslim Edildi">Teslim Edildi</option>
                                <option value="Iptal">İptal (Stok İade Edilir!)</option>
                            </select>
                            <input type="submit" name="siparis_guncelle" value="Güncelle">
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    </body>
</html>