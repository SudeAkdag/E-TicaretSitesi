<?php
// /personel/dashboard.php
session_start();
include '../db_config.php'; 

// 1. Yetki Kontrolü: Sadece Personel (Rol ID: 2)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$mesaj = '';
$hata = '';

// 2. Sipariş Durumu Güncelleme İşlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['siparis_guncelle'])) {
    $siparis_id = $_POST['siparis_id'];
    $yeni_durum = $_POST['yeni_durum'];

    if ($stmt = $conn->prepare("CALL SP_SiparisDurumGuncelle(?, ?)")) {
        $stmt->bind_param("is", $siparis_id, $yeni_durum);
        if ($stmt->execute()) {
            $mesaj = "✅ Sipariş #$siparis_id durumu başarıyla **'$yeni_durum'** olarak güncellendi.";
        } else {
            $hata = "Güncelleme başarısız: " . $stmt->error;
        }
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) { ; } // Temizlik
    } else {
        $hata = "Sorgu hatası: " . $conn->error;
    }
}

// 3. Bekleyen Siparişleri Listeleme (SP_BeklemedeOlanSiparisler)
if ($stmt = $conn->prepare("CALL SP_BeklemedeOlanSiparisler()")) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $siparisler[] = $row;
        }
        $stmt->close();
    } else {
        $hata = "Sipariş listesi alınamadı: " . $stmt->error;
    }
    while ($conn->more_results() && $conn->next_result()) { ; } // Temizlik
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Paneli - Sipariş Yönetimi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Detay butonu için özel stil */
        .btn-detail {
            display: inline-flex; align-items: center; gap: 5px;
            background-color: #3b82f6; color: white; 
            padding: 6px 12px; border-radius: 6px; 
            text-decoration: none; font-size: 13px; font-weight: 500;
            transition: background 0.2s;
        }
        .btn-detail:hover { background-color: #2563eb; }
    </style>
</head>
<body>

<div class="page-container fade-in">
    
    <?php include 'menu.php'; ?>

    <div class="header-info" style="margin-top: 10px;">
        <div>
            <h1 style="margin-top:0;">📋 Sipariş Yönetimi</h1>
        </div>
    </div>

    <?php if ($mesaj): ?>
        <div class="message-box success">
            <?php echo $mesaj; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($hata): ?>
        <div class="message-box error">
            <strong>⚠️ Hata:</strong> <?php echo htmlspecialchars($hata); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($siparisler)): ?>
        <div class="card text-center">
            <h3>Şu An Bekleyen Sipariş Yok</h3>
            <p style="color: var(--text-secondary);">İşlem bekleyen yeni sipariş bulunmamaktadır. ✅</p>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Sipariş No</th>
                        <th>Tarih</th>
                        <th>Tutar</th>
                        <th>Müşteri</th>
                        <th>Teslimat Adresi</th>
                        <th>Durum</th>
                        <th>İşlem</th> <th>Detay</th> </tr>
                </thead>
                <tbody>
                    <?php foreach ($siparisler as $siparis): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($siparis['SiparisID']); ?></strong></td>
                        
                        <td><?php echo date("d.m.Y", strtotime($siparis['SiparisTarihi'])); ?></td>
                        <td><strong><?php echo number_format($siparis['ToplamTutar'], 2); ?> ₺</strong></td>
                        
                        <td>
                            <?php echo htmlspecialchars($siparis['MusteriAd'] . ' ' . $siparis['MusteriSoyad']); ?><br>
                            <small style="color: #64748b;"><?php echo htmlspecialchars($siparis['MusteriEmail']); ?></small>
                        </td>
                        
                        <td title="<?php echo htmlspecialchars($siparis['TeslimatAdresi']); ?>">
                            <?php echo mb_strimwidth($siparis['TeslimatAdresi'], 0, 30, "..."); ?>
                        </td>
                        
                        <td>
                            <span class="badge badge-warning"><?php echo htmlspecialchars($siparis['Durum']); ?></span>
                        </td>
                        
                        <td>
                            <form method="POST" style="display: flex; gap: 5px;">
                                <input type="hidden" name="siparis_id" value="<?php echo $siparis['SiparisID']; ?>">
                                <select name="yeni_durum" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc;">
                                    <option value="" disabled selected>Seç</option>
                                    <option value="Hazirlaniyor">📦 Hazırlanıyor</option>
                                    <option value="Kargoda">🚚 Kargoda</option>
                                    <option value="Teslim Edildi">✅ Teslim Edildi</option>
                                    <option value="Iptal">❌ İptal</option>
                                </select>
                                <button type="submit" name="siparis_guncelle" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">OK</button>
                            </form>
                        </td>

                        <td>
                            <a href="siparis_detay.php?id=<?php echo $siparis['SiparisID']; ?>" class="btn-detail">
                                📄 Fatura
                            </a>
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