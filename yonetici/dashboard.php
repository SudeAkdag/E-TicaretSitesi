<?php
// /yonetici/dashboard.php
session_start();
require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}
    
// Yetki Kontrolü: Yönetici (1)
if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 1) {
    header("location: ../login.php"); exit;
}

$yonetici_adi = isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Yönetici';
$sorgu_sonucu = [];
$sehir_sonucu = [];
$hata = '';

// 1. RAPOR: En Çok Satan Ürünler (SP_EnCokSatanUrunler)
try {
    $stmt = $conn->prepare("CALL SP_EnCokSatanUrunler()");
    if ($stmt->execute()) {
        // PDO'da get_result() yerine direkt fetchAll() kullanılır
        $sorgu_sonucu = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Procedure sonrası imleci serbest bırak (MySQLi bugfix yerine geçer)
        $stmt->closeCursor();
    }
} catch (PDOException $e) {
    $hata .= "Rapor 1 hatası: " . $e->getMessage();
}

// 2. RAPOR: Şehir Bazlı Analiz (SP_SehirBazliSatisAnalizi)
try {
    $stmt2 = $conn->prepare("CALL SP_SehirBazliSatisAnalizi()");
    if ($stmt2->execute()) {
        $sehir_sonucu = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $stmt2->closeCursor();
    }
} catch (PDOException $e) {
    $hata .= " Rapor 2 hatası: " . $e->getMessage();
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
        /* Tablo görünümü iyileştirmesi */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); color: #333; }
        h3 { color: #2563eb; margin-bottom: 15px; border-bottom: 2px solid #f0f0f0; padding-bottom: 5px; }
    </style>
</head>
<body>

<div class="page-container fade-in">
    <?php include 'menu.php'; ?>

    <div class="header">
        <div>
            <h1>👑 Yönetim Paneli</h1>
            <p>Hoş Geldiniz, <strong><?php echo htmlspecialchars($yonetici_adi); ?></strong></p>
        </div>
    </div>

    <?php if ($hata): ?>
        <div class="alert alert-error" style="background:#fee2e2; color:#b91c1c; padding:15px; border-radius:8px; margin:20px 0;">
            <strong>⚠️ Hata:</strong> <?php echo $hata; ?>
        </div>
    <?php endif; ?>

    <div class="grid-container">
        
        <div class="card">
            <h3>🏆 En Çok Satan Ürünler</h3>
            <?php if (empty($sorgu_sonucu)): ?>
                <p>Henüz satış verisi bulunamadı.</p>
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
                            <td><span class="badge badge-success" style="background:#22c55e; color:white; padding:4px 8px; border-radius:4px;"><?php echo $urun['ToplamSatilanAdet']; ?></span></td>
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
                <p>Henüz analiz verisi bulunamadı.</p>
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
                           <td><?php echo htmlspecialchars(mb_strimwidth($sehir['Lokasyon'], 0, 25, "...")); ?></td>
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