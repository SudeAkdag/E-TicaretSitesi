<?php
// /musteri/dashboard.php

include '../db_config.php'; 

// Yetki Kontrolü: Rol ID'si 3 olmalı
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 3) {
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$hata = '';

// Müşteri ID'sini Session'dan al
$kullanici_id = $_SESSION['kullanici_id']; 

// MüşteriID'yi bul (KULLANICI 1-1 MUSTERI ilişkisi nedeniyle)
$result_musteri = $conn->query("SELECT MusteriID FROM MUSTERI WHERE KullaniciID = $kullanici_id");
$musteri_info = $result_musteri->fetch_assoc();
$musteri_id = $musteri_info['MusteriID'];

if ($musteri_id) {
    // Saklı Yordam: SP_MusteriSiparisDetaylari (5 farklı tabloyu JOINler)
    if ($stmt = $conn->prepare("CALL SP_MusteriSiparisDetaylari(?)")) {
        $stmt->bind_param("i", $musteri_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            // Sonuçları SiparişID'ye göre gruplayarak listele
            $temp_siparisler = [];
            while ($row = $result->fetch_assoc()) {
                $siparis_id = $row['SiparisID'];
                if (!isset($temp_siparisler[$siparis_id])) {
                    $temp_siparisler[$siparis_id] = [
                        'SiparisID' => $row['SiparisID'],
                        'SiparisTarihi' => $row['SiparisTarihi'],
                        'ToplamTutar' => $row['ToplamTutar'],
                        'SiparisDurumu' => $row['SiparisDurumu'],
                        'AcikAdres' => $row['AcikAdres'],
                        'Detaylar' => []
                    ];
                }
                $temp_siparisler[$siparis_id]['Detaylar'][] = [
                    'UrunAdi' => $row['UrunAdi'],
                    'Adet' => $row['Adet'],
                    'BirimFiyat' => $row['BirimFiyat']
                ];
            }
            $siparisler = $temp_siparisler;
            $stmt->close();
        } else {
            $hata = "Sipariş detayları çekme hatası: " . $stmt->error;
        }

        while ($conn->more_results() && $conn->next_result()) { ; } // Bugfix

    } else {
        $hata = "Saklı Yordam hazırlama hatası: " . $conn->error;
    }
} else {
    $hata = "Kullanıcı ID'sine karşılık gelen müşteri bulunamadı.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Müşteri Paneli</title>
</head>
<body>
    <h1>🛒 Müşteri Paneli</h1>
    <p>Hoş Geldiniz, Müşteri (<?php echo htmlspecialchars($_SESSION['email']); ?>)!</p>
    <p><a href="../logout.php">Çıkış Yap</a></p>

    <h2>Sipariş Geçmişim</h2>
    <?php if ($hata): ?>
        <p style='color:red;'>Hata: <?php echo $hata; ?></p>
    <?php elseif (empty($siparisler)): ?>
        <p>Henüz tamamlanmış bir siparişiniz bulunmamaktadır.</p>
    <?php else: ?>
        <?php foreach ($siparisler as $siparis): ?>
            <div style="border: 1px solid #ccc; margin-bottom: 20px; padding: 15px;">
                <h3>Sipariş ID: <?php echo $siparis['SiparisID']; ?> (Durum: <strong><?php echo $siparis['SiparisDurumu']; ?></strong>)</h3>
                <p>Tarih: <?php echo $siparis['SiparisTarihi']; ?> | Toplam Tutar: **<?php echo number_format($siparis['ToplamTutar'], 2); ?> TL**</p>
                <p>Teslimat Adresi: <em><?php echo htmlspecialchars($siparis['AcikAdres']); ?></em></p>
                
                <h4>Ürün Detayları:</h4>
                <table border="1" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Adet</th>
                            <th>Birim Fiyatı</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siparis['Detaylar'] as $detay): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detay['UrunAdi']); ?></td>
                                <td><?php echo htmlspecialchars($detay['Adet']); ?></td>
                                <td><?php echo number_format($detay['BirimFiyat'], 2); ?> TL</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    </body>
</html>