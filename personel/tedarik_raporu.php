<?php
// /personel/tedarik_raporu.php
session_start();
require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php"); exit;
}

$tedarikci_verileri = [];
$hata = "";

// Saklı Yordamı (Stored Procedure) Çağır (PDO Yöntemi)
try {
    $sql = "CALL SP_TedarikciSattigiUrunSayisi()";
    $stmt = $conn->query($sql);
    
    // PDO'da fetch_assoc yerine fetchAll(PDO::FETCH_ASSOC) ile tüm sonuçları diziye alıyoruz
    $tedarikci_verileri = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procedure sonrası bağlantıyı serbest bırak
    $stmt->closeCursor();
} catch (PDOException $e) {
    $hata = "Rapor çekilirken bir hata oluştu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tedarikçi Raporu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-container">
        <?php include 'menu.php'; ?>

        <div class="header">
            <div>
                <h1> Tedarikçi Analiz Raporu</h1>
            </div>
        </div>

        <?php if ($hata): ?>
            <div class="alert alert-error" style="color: #ef4444; padding: 10px; background: #fef2f2; border-radius: 6px; margin: 10px 0;">
                <?php echo $hata; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left; padding: 12px; border-bottom: 2px solid #e2e8f0;">Tedarikçi Adı</th>
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Yetkili</th>
                        <th style="padding: 12px; border-bottom: 2px solid #e2e8f0;">Ürün Çeşit Sayısı</th>
                        <th style="text-align:left; padding: 12px; border-bottom: 2px solid #e2e8f0;">Kategoriler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tedarikci_verileri)): ?>
                        <tr><td colspan="4" style="text-align:center; padding: 20px;">Veri bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach($tedarikci_verileri as $row): ?>
                        <tr>
                            <td style="font-weight:bold; color: black; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                                 <?php echo htmlspecialchars($row['TedarikciAdi']); ?>
                            </td>
                            
                            <td style="padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                <?php echo htmlspecialchars($row['TedarikciSoyadi']); ?>
                            </td>
                            
                            <td style="text-align:center; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                                <span class="status-active" style="font-size:14px; padding: 4px 12px; background: #dcfce7; color: #166534; border-radius: 999px;">
                                    <?php echo $row['TedarikEdilenUrunCesidi']; ?> Adet
                                </span>
                            </td>

                            <td style="color: black; font-size:13px; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                                <?php echo htmlspecialchars($row['TedarikEdilenKategoriler']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>