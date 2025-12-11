<?php
// /personel/dashboard.php

// 5. Madde: Session başlatıyoruz.
session_start();

include '../db_config.php'; 

// c) Madde: Yetki Kontrolü. Sadece Rol ID'si 2 (Personel) olanlar girebilir.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$mesaj = '';
$hata = '';
$personel_adi = isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Personel';

// A) Sipariş Durumu Güncelleme İşlemi (Form POST edildiğinde)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['siparis_guncelle'])) {
    $siparis_id = $_POST['siparis_id'];
    $yeni_durum = $_POST['yeni_durum'];

    // 7. Madde: Veri güncelleme işlemi Saklı Yordam (SP_SiparisDurumGuncelle) ile yapılıyor.
    if ($stmt = $conn->prepare("CALL SP_SiparisDurumGuncelle(?, ?)")) {
        $stmt->bind_param("is", $siparis_id, $yeni_durum);
        
        if ($stmt->execute()) {
            $mesaj = "✅ Sipariş #$siparis_id durumu başarıyla **'$yeni_durum'** olarak güncellendi.";
            // Not: Bu SP'nin arkasında stokları yöneten bir TRIGGER çalışıyor olabilir.
        } else {
            $hata = "Güncelleme başarısız: " . $stmt->error;
        }
        $stmt->close();
        
        // MySQLi Bugfix: SP çağrısından sonra bağlantıyı temizle (sonraki sorgular için şart)
        while ($conn->more_results() && $conn->next_result()) { ; }

    } else {
        $hata = "Sorgu hazırlama hatası: " . $conn->error;
    }
}

// B) Bekleyen Siparişleri Listeleme
// 6. Madde: JOIN içeren sorgu. Bu SP arka planda Siparis, Musteri, Kullanici ve Adres tablolarını birleştirir.
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
    // Bağlantı temizliği
    while ($conn->more_results() && $conn->next_result()) { ; }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Personel Paneli - Sipariş Yönetimi</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f6f9; margin: 20px; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header-info { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .logout-btn { background-color: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e9ecef; }
        
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.85em; font-weight: bold; background-color: #ffc107; color: #333; }
        
        select { padding: 5px; border-radius: 4px; border: 1px solid #ccc; }
        .btn-update { background-color: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
        .btn-update:hover { background-color: #218838; }
        
        .message-box { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-info">
        <div>
            <h1>📦 Depo & Sipariş Yönetimi</h1>
            <p>Aktif Personel: <strong><?php echo htmlspecialchars($personel_adi); ?></strong></p>
        </div>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="message-box success"><?php echo $mesaj; ?></div>
    <?php endif; ?>
    
    <?php if ($hata): ?>
        <div class="message-box error"><?php echo $hata; ?></div>
    <?php endif; ?>

    <h3>📋 Bekleyen Siparişler Listesi</h3>
    
    <?php if (empty($siparisler)): ?>
        <p style="text-align:center; padding: 20px; font-style: italic;">Şu an işlem bekleyen yeni sipariş bulunmamaktadır.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Sipariş No</th>
                    <th>Tarih</th>
                    <th>Tutar</th>
                    <th>Müşteri Bilgisi</th>
                    <th>Teslimat Adresi</th>
                    <th>Mevcut Durum</th>
                    <th>Durum Güncelle</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siparisler as $siparis): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($siparis['SiparisID']); ?></td>
                    <td><?php echo htmlspecialchars($siparis['SiparisTarihi']); ?></td>
                    <td><?php echo number_format($siparis['ToplamTutar'], 2); ?> ₺</td>
                    <td>
                        <?php echo htmlspecialchars($siparis['MusteriAd'] . ' ' . $siparis['MusteriSoyad']); ?><br>
                        <small>(<?php echo htmlspecialchars($siparis['MusteriEmail']); ?>)</small>
                    </td>
                    <td title="<?php echo htmlspecialchars($siparis['TeslimatAdresi']); ?>">
                        <?php echo htmlspecialchars(substr($siparis['TeslimatAdresi'], 0, 40)) . '...'; ?>
                    </td>
                    <td><span class="status-badge"><?php echo htmlspecialchars($siparis['Durum']); ?></span></td>
                    <td>
                        <form method="POST" action="dashboard.php">
                            <input type="hidden" name="siparis_id" value="<?php echo $siparis['SiparisID']; ?>">
                            <div style="display:flex; gap:5px;">
                                <select name="yeni_durum" required>
                                    <option value="" disabled selected>Seçiniz</option>
                                    <option value="Hazirlaniyor">📦 Hazırlanıyor</option>
                                    <option value="Kargoda">🚚 Kargoda</option>
                                    <option value="Teslim Edildi">✅ Teslim Edildi</option>
                                    <option value="Iptal">❌ İptal Et</option>
                                </select>
                                <button type="submit" name="siparis_guncelle" class="btn-update">Kaydet</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>