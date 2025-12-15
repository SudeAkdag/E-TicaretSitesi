<?php
// /yonetici/dashboard.php
session_start();
include '../db_config.php'; 

// Yetki Kontrolü: Yönetici (1)
if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 1) {
    header("location: ../login.php"); exit;
}

$yonetici_adi = isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Yönetici';
$sorgu_sonucu = [];
$sehir_sonucu = [];
$hata = '';

// 1. RAPOR: En Çok Satan Ürünler (SP_EnCokSatanUrunler)
if ($stmt = $conn->prepare("CALL SP_EnCokSatanUrunler()")) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $sorgu_sonucu[] = $row;
        }
        $stmt->close();
    }
    // Bağlantı temizliği (Çok önemli!)
    while ($conn->more_results() && $conn->next_result()) { ; }
}

// 2. RAPOR: Şehir Bazlı Analiz (SP_SehirBazliSatisAnalizi)
if ($stmt2 = $conn->prepare("CALL SP_SehirBazliSatisAnalizi()")) {
    if ($stmt2->execute()) {
        $result2 = $stmt2->get_result();
        while ($row = $result2->fetch_assoc()) {
            $sehir_sonucu[] = $row;
        }
        $stmt2->close();
    }
    while ($conn->more_results() && $conn->next_result()) { ; }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yönetici Paneli</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .grid-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        @media (max-width: 768px) { .grid-container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="page-container fade-in">
    <div class="header">
        <div>
            <h1>👑 Yönetim Paneli</h1>
            <p>Hoş Geldiniz, <strong><?php echo htmlspecialchars($yonetici_adi); ?></strong></p>
        </div>
        <a href="../logout.php" class="logout-btn">🚪 Çıkış</a>
    </div>

    <div class="grid-container">
        
        <div class="card">
            <h3>🏆 En Çok Satan Ürünler</h3>
            <?php if (empty($sorgu_sonucu)): ?>
                <p>Veri bulunamadı.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Satış Adedi</th>
                            <th>Müşteri Sayısı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sorgu_sonucu as $urun): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($urun['UrunAdi']); ?></td>
                            <td><span class="badge badge-success"><?php echo $urun['ToplamSatilanAdet']; ?></span></td>
                            <td>👤 <?php echo $urun['FarkliMusteriSayisi']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>📍 Şehir Bazlı Ciro Analizi</h3>
            <?php if (empty($sehir_sonucu)): ?>
                <p>Veri bulunamadı.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Lokasyon (Adres)</th>
                            <th>Sipariş</th>
                            <th style="text-align:right;">Ciro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sehir_sonucu as $sehir): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($sehir['AcikAdres'], 0, 25)) . '...'; ?></td>
                            <td><?php echo $sehir['SiparisSayisi']; ?></td>
                            <td style="text-align:right; font-weight:bold; color:#f97316;">
                                <?php echo number_format($sehir['ToplamCiro'], 2); ?> ₺
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

    </div>
</div>
</body>
</html>