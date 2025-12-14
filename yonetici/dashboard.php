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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Satış Raporları</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="page-container fade-in">
    <div class="header">
        <div>
            <h1>👑 Yönetim Paneli</h1>
            <p>Hoş Geldiniz, Sn. <strong><?php echo htmlspecialchars($yonetici_adi); ?></strong></p>
        </div>
        <a href="../logout.php" class="logout-btn">🚪 Güvenli Çıkış</a>
    </div>

    <div class="stat-card">
        <strong>📊 Rapor Türü:</strong> En Çok Satan Ürünler Analizi<br>
        <small>Veriler anlık olarak veritabanından çekilmektedir.</small>
    </div>
    
    <?php if ($hata): ?>
        <div class="alert alert-error">
            <strong>⚠️ Hata:</strong> <?php echo htmlspecialchars($hata); ?>
        </div>
    <?php elseif (empty($sorgu_sonucu)): ?>
        <div class="card text-center">
            <h3>Henüz Yeterli Veri Yok</h3>
            <p style="color: var(--text-secondary);">
                Henüz yeterli satış verisi oluşmadı. Rapor görüntülenebilmesi için daha fazla sipariş gerekmektedir.
            </p>
        </div>
    <?php else: ?>
        <h2>📈 Satış Performans Raporu</h2>
        <div class="table-container">
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
                    <?php 
                    $max_satis = 0;
                    foreach ($sorgu_sonucu as $urun) {
                        if ($urun['ToplamSatilanAdet'] > $max_satis) {
                            $max_satis = $urun['ToplamSatilanAdet'];
                        }
                    }
                    foreach ($sorgu_sonucu as $urun): 
                        $performans_yuzde = $max_satis > 0 ? ($urun['ToplamSatilanAdet'] / $max_satis) * 100 : 0;
                    ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($urun['UrunID']); ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($urun['UrunAdi']); ?></strong></td>
                        <td>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($urun['ToplamSatilanAdet']); ?> Adet</span>
                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars($urun['FarkliMusteriSayisi']); ?> Kişi</span>
                        </td>
                        <td style="min-width: 150px;">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo min($performans_yuzde, 100); ?>%;"></div>
                            </div>
                            <small style="color: var(--text-secondary); margin-top: 0.25rem; display: block;">
                                <?php echo number_format($performans_yuzde, 1); ?>%
                            </small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>