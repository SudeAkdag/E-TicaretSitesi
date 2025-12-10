<?php
// /yonetici/dashboard.php

include '../db_config.php'; 

// Yetki Kontrolü: Rol ID'si 1 olmalı
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 1) {
    header("location: ../login.php");
    exit;
}

$sorgu_sonucu = [];
$hata = '';

// Saklı Yordam: SP_EnCokSatanUrunler (URUN, SIPARISDETAY, SIPARIS tabloları)
if ($stmt = $conn->prepare("CALL SP_EnCokSatanUrunler()")) {
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sorgu_sonucu[] = $row;
        }
        $stmt->close();
    } else {
        $hata = "Rapor çekme hatası: " . $stmt->error;
    }
    
    // MySQLi bugfix: Saklı yordamdan sonra kalan sonuç kümesini temizle
    while ($conn->more_results() && $conn->next_result()) { ; }

} else {
    $hata = "Saklı Yordam hazırlama hatası: " . $conn->error;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Yönetici Paneli</title>
</head>
<body>
    <h1>👑 Yönetici Paneli</h1>
    <p>Hoş Geldiniz, Yönetici (<?php echo htmlspecialchars($_SESSION['email']); ?>)!</p>
    <p><a href="../logout.php">Çıkış Yap</a></p>
    
    <h2>En Çok Satan Ürünler Raporu</h2>
    <p>Bu rapor, 3 farklı tabloyu birleştiren karmaşık bir JOIN sorgusu ile elde edilmiştir.</p>
    
    <?php if ($hata): ?>
        <p style='color:red;'>Hata: <?php echo $hata; ?></p>
    <?php elseif (empty($sorgu_sonucu)): ?>
        <p>Henüz satış verisi bulunmamaktadır.</p>
    <?php else: ?>
        <table border="1" style="width: 60%;">
            <thead>
                <tr>
                    <th>Ürün ID</th>
                    <th>Ürün Adı</th>
                    <th>Toplam Satılan Adet</th>
                    <th>Farklı Müşteri Sayısı</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sorgu_sonucu as $urun): ?>
                <tr>
                    <td><?php echo htmlspecialchars($urun['UrunID']); ?></td>
                    <td><?php echo htmlspecialchars($urun['UrunAdi']); ?></td>
                    <td><?php echo htmlspecialchars($urun['ToplamSatilanAdet']); ?></td>
                    <td><?php echo htmlspecialchars($urun['FarkliMusteriSayisi']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    </body>
</html>