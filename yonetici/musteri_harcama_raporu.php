<?php
// /yonetici/musteri_harcama_raporu.php
session_start();

// DÜZELTİLEN BAĞLANTI YOLU
include '../db_config.php'; 

if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 1) {
    header("location: ../login.php"); exit;
}

$rapor_verisi = [];
// Cursor ile raporu çekiyoruz
if ($result = $conn->query("CALL SP_MusteriHarcamaRaporu_Cursor()")) {
    while ($row = $result->fetch_assoc()) {
        $rapor_verisi[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Sadakat Raporu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-container fade-in">
    
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <div class="navbar" style="background-color: #1e293b; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="font-weight: bold; color: white;">👑 Yönetici Paneli</div>
        <div class="nav-links" style="display: flex; gap: 15px;">
            <a href="dashboard.php" style="color: #e2e8f0; text-decoration: none;">📊 Genel Durum</a>
            <a href="sehir_analiz.php" style="color: #e2e8f0; text-decoration: none;">📍 Şehir Analizi</a>
            <a href="musteri_harcama_raporu.php" style="color: white; text-decoration: none; font-weight: bold; background: #3b82f6; padding: 5px 10px; border-radius: 4px;">📈 Müşteri Sadakat Raporu</a>
            <a href="../logout.php" style="color: white; background: #ef4444; padding: 5px 10px; border-radius: 4px; text-decoration: none;">🚪 Çıkış</a>
        </div>
    </div>

    <div class="card">
        <h2>📈 Müşteri Sadakat (VIP) Raporu</h2>
        <p>Bu rapor, toplam harcaması 5000 ₺ üzerinde olan müşterileri VIP olarak sınıflandırır.</p>
        
        <table>
            <thead>
                <tr>
                    <th>Müşteri Bilgisi</th>
                    <th>Toplam Harcama</th>
                    <th>Durum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rapor_verisi as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['MusteriBilgi']); ?></td>
                    <td><?php echo number_format($row['HarcamaTutari'], 2); ?> ₺</td>
                    <td>
                        <span class="badge <?php echo ($row['Durum'] == 'VIP Müşteri') ? 'badge-success' : 'badge-info'; ?>">
                            <?php echo $row['Durum']; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>