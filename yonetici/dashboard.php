<?php
// /yonetici/dashboard.php

// 5. Madde: Session başlatıyoruz.
session_start();

include '../db_config.php'; 

// c) Madde: Yetki Kontrolü. Sadece Rol ID'si 1 (Yönetici) olanlar girebilir.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 1) {
    header("location: ../login.php");
    exit;
}

$sorgu_sonucu = [];
$hata = '';
$yonetici_adi = isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Yönetici';

// 6. ve 7. Madde: Stored Procedure ve Join Kullanımı
// SP_EnCokSatanUrunler; URUN, SIPARISDETAY ve SIPARIS tablolarını JOIN ile birleştirip analiz eder.
if ($stmt = $conn->prepare("CALL SP_EnCokSatanUrunler()")) {
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sorgu_sonucu[] = $row;
        }
        $stmt->close();
    } else {
        $hata = "Rapor verileri çekilemedi: " . $stmt->error;
    }
    
    // MySQLi bugfix: Bağlantıyı sonraki sorgular için temizle
    while ($conn->more_results() && $conn->next_result()) { ; }

} else {
    $hata = "Sistem hatası (SP Hazırlama): " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli - Satış Raporları</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #2c3e50; color: #ecf0f1; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background-color: #34495e; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e74c3c; padding-bottom: 20px; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 24px; }
        .logout-btn { background-color: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; transition: background 0.3s; }
        .logout-btn:hover { background-color: #c0392b; }

        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #ecf0f1; color: #2c3e50; border-radius: 5px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #bdc3c7; }
        th { background-color: #2980b9; color: white; text-transform: uppercase; font-size: 0.9em; }
        tr:nth-child(even) { background-color: #dfe6e9; }
        tr:hover { background-color: #b2bec3; }

        .stat-card { background-color: #16a085; padding: 15px; border-radius: 5px; margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1>👑 Yönetim Paneli</h1>
            <p>Hoş Geldiniz, Sn. <strong><?php echo htmlspecialchars($yonetici_adi); ?></strong></p>
        </div>
        <a href="../logout.php" class="logout-btn">Güvenli Çıkış</a>
    </div>

    <div class="stat-card">
        <strong>Rapor Türü:</strong> En Çok Satan Ürünler Analizi<br>
        <small>Veriler anlık olarak veritabanından çekilmektedir.</small>
    </div>
    
    <?php if ($hata): ?>
        <p style='color:#e74c3c; background: #fff; padding: 10px; border-radius: 5px;'>Hata: <?php echo $hata; ?></p>
    <?php elseif (empty($sorgu_sonucu)): ?>
        <p>Henüz yeterli satış verisi oluşmadı.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Ürün ID</th>
                    <th>Ürün Adı</th>
                    <th>Toplam Satış (Adet)</th>
                    <th>Erişilen Müşteri Sayısı</th>
                    <th>Performans</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sorgu_sonucu as $urun): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($urun['UrunID']); ?></td>
                    <td><strong><?php echo htmlspecialchars($urun['UrunAdi']); ?></strong></td>
                    <td><?php echo htmlspecialchars($urun['ToplamSatilanAdet']); ?> Adet</td>
                    <td><?php echo htmlspecialchars($urun['FarkliMusteriSayisi']); ?> Kişi</td>
                    <td>
                        <div style="background-color: #bdc3c7; width: 100px; height: 10px; border-radius: 5px;">
                            <div style="background-color: #27ae60; width: <?php echo min($urun['ToplamSatilanAdet'] * 2, 100); ?>%; height: 100%; border-radius: 5px;"></div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>