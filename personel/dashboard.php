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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personel Paneli - Sipariş Yönetimi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="page-container fade-in">
    <div class="header-info">
        <div>
            <h1>📦 Depo & Sipariş Yönetimi</h1>
            <p>Aktif Personel: <strong><?php echo htmlspecialchars($personel_adi); ?></strong></p>
        </div>
        <a href="../logout.php" class="logout-btn">🚪 Çıkış Yap</a>
    </div>

    <?php if ($mesaj): ?>
        <div class="message-box success">
            <strong>✅ Başarılı:</strong> <?php echo htmlspecialchars($mesaj); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($hata): ?>
        <div class="message-box error">
            <strong>⚠️ Hata:</strong> <?php echo htmlspecialchars($hata); ?>
        </div>
    <?php endif; ?>

    <h2>📋 Bekleyen Siparişler Listesi</h2>
    
    <?php if (empty($siparisler)): ?>
        <div class="card text-center">
            <h3>Şu An Bekleyen Sipariş Yok</h3>
            <p style="font-style: italic; color: var(--text-secondary);">
                İşlem bekleyen yeni sipariş bulunmamaktadır. Tüm siparişler işlenmiş durumda! ✅
            </p>
        </div>
    <?php else: ?>
        <div class="table-container">
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
                        <td><strong>#<?php echo htmlspecialchars($siparis['SiparisID']); ?></strong></td>
                        <td><?php echo date("d.m.Y", strtotime($siparis['SiparisTarihi'])); ?></td>
                        <td><strong><?php echo number_format($siparis['ToplamTutar'], 2); ?> ₺</strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($siparis['MusteriAd'] . ' ' . $siparis['MusteriSoyad']); ?></strong><br>
                            <small style="color: var(--text-secondary);"><?php echo htmlspecialchars($siparis['MusteriEmail']); ?></small>
                        </td>
                        <td title="<?php echo htmlspecialchars($siparis['TeslimatAdresi']); ?>">
                            <?php 
                            $adres = htmlspecialchars($siparis['TeslimatAdresi']);
                            echo strlen($adres) > 50 ? substr($adres, 0, 50) . '...' : $adres;
                            ?>
                        </td>
                        <td>
                            <span class="badge badge-warning"><?php echo htmlspecialchars($siparis['Durum']); ?></span>
                        </td>
                        <td>
                            <form method="POST" action="dashboard.php" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="siparis_id" value="<?php echo $siparis['SiparisID']; ?>">
                                <select name="yeni_durum" required style="flex: 1; padding: 0.5rem;">
                                    <option value="" disabled selected>Durum Seçiniz</option>
                                    <option value="Hazirlaniyor">📦 Hazırlanıyor</option>
                                    <option value="Kargoda">🚚 Kargoda</option>
                                    <option value="Teslim Edildi">✅ Teslim Edildi</option>
                                    <option value="Iptal">❌ İptal Et</option>
                                </select>
                                <button type="submit" name="siparis_guncelle" class="btn btn-success" style="padding: 0.5rem 1rem; white-space: nowrap;">
                                    Kaydet
                                </button>
                            </form>
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