<?php
// /yonetici/musteri_harcama_raporu.php
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

$rapor_verisi = [];
$hata = '';

// Cursor ile raporu çekiyoruz (PDO Yöntemi)
try {
    // query() yerine prepare/execute kullanmak SP'ler için daha sağlıklıdır
    $stmt = $conn->prepare("CALL SP_MusteriHarcamaRaporu_Cursor()");
    if ($stmt->execute()) {
        // PDO'da fetch_assoc yerine fetchAll(PDO::FETCH_ASSOC)
        $rapor_verisi = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // SP sonrası imleci serbest bırak (Bağlantı temizliği)
        $stmt->closeCursor();
    }
} catch (PDOException $e) {
    $hata = "Rapor alınırken hata oluştu: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Sadakat Raporu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Badge renkleri için ek stil */
        .badge-success { background-color: #22c55e; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-info { background-color: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        th { background-color: #f8fafc; color: #64748b; }
    </style>
</head>
<body>
<div class="page-container fade-in">
    
    <div class="navbar" style="background-color: #1e293b; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-weight: bold; color: white;">👑 Yönetici Paneli</div>
        <div class="nav-links" style="display: flex; gap: 15px;">
            <a href="dashboard.php" style="color: #e2e8f0; text-decoration: none;">📊 Genel Durum</a>
            <a href="musteri_harcama_raporu.php" style="color: white; text-decoration: none; font-weight: bold; background: #3b82f6; padding: 5px 10px; border-radius: 4px;">📈 Müşteri Sadakat Raporu</a>
            <a href="../logout.php" style="color: white; background: #ef4444; padding: 5px 10px; border-radius: 4px; text-decoration: none;">🚪 Çıkış</a>
        </div>
    </div>

    <div class="card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h2 style="color: #1e293b; margin-bottom: 10px;">📈 Müşteri Sadakat (VIP) Raporu</h2>
        <p style="color: #64748b; font-size: 14px;">Bu rapor, toplam harcaması 5000 ₺ üzerinde olan müşterileri VIP olarak sınıflandırır.</p>
        
        <?php if ($hata): ?>
            <div class="alert error" style="color: #ef4444; padding: 10px; background: #fef2f2; border-radius: 6px; margin: 10px 0;"><?php echo $hata; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Müşteri Bilgisi</th>
                    <th>Toplam Harcama</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rapor_verisi)): ?>
                    <tr><td colspan="3" style="text-align:center;">Görüntülenecek veri bulunamadı.</td></tr>
                <?php else: ?>
                    <?php foreach ($rapor_verisi as $row): ?>
                    <tr>
                        <td style="font-weight: 500; color: #0f172a;"><?php echo htmlspecialchars($row['MusteriBilgi']); ?></td>
                        <td style="color: #0f172a;"><?php echo number_format($row['HarcamaTutari'], 2); ?> ₺</td>
                        <td>
                            <span class="badge <?php echo ($row['Durum'] == 'VIP Müşteri') ? 'badge-success' : 'badge-info'; ?>">
                                <?php echo $row['Durum']; ?>
                            </span>
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