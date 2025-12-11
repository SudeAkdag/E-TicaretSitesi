<?php
// /musteri/dashboard.php
// 5. Madde: Sayfa geçişlerinde session kontrolü
session_start();

include '../db_config.php'; 

// c) Madde: Yetki Kontrolü. Sadece Rol ID'si 3 (Müşteri) olanlar girebilir.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 3) {
    // Yetkisiz giriş denemesi, login sayfasına at.
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$hata = '';

// Session'dan kullanıcı bilgilerini al
$kullanici_id = $_SESSION['kullanici_id']; 
$ad_soyad = isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Değerli Müşterimiz';

// 1. Adım: KullanıcıID'den MusteriID'yi bul.
// (Normalde bunu da login aşamasında session'a atabilirdik ama veritabanından çekmek de uygundur)
$sql_musteri = "SELECT MusteriID FROM MUSTERI WHERE KullaniciID = $kullanici_id";
$result_musteri = $conn->query($sql_musteri);

if ($result_musteri->num_rows > 0) {
    $musteri_info = $result_musteri->fetch_assoc();
    $musteri_id = $musteri_info['MusteriID'];

    // 6. ve 7. Madde: Saklı Yordam (SP) ve Join Kullanımı
    // SP_MusteriSiparisDetaylari prosedürü; Siparis, SiparisDetay, Urun ve Adres tablolarını JOIN ile birleştirip getirmelidir.
    if ($stmt = $conn->prepare("CALL SP_MusteriSiparisDetaylari(?)")) {
        $stmt->bind_param("i", $musteri_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            // Veriyi Gruplama Algoritması (Master-Detail Yapısı)
            // SQL'den gelen satırları Sipariş ID'sine göre gruplayıp diziye atıyoruz.
            $temp_siparisler = [];
            while ($row = $result->fetch_assoc()) {
                $siparis_id = $row['SiparisID'];
                
                // Eğer bu sipariş dizide yoksa, başlık bilgilerini oluştur
                if (!isset($temp_siparisler[$siparis_id])) {
                    $temp_siparisler[$siparis_id] = [
                        'SiparisID' => $row['SiparisID'],
                        'SiparisTarihi' => $row['SiparisTarihi'],
                        'ToplamTutar' => $row['ToplamTutar'],
                        'SiparisDurumu' => $row['SiparisDurumu'],
                        'AcikAdres' => $row['AcikAdres'], // Adres tablosundan gelen veri
                        'Detaylar' => [] // Ürünleri buraya dolduracağız
                    ];
                }
                
                // Siparişin içindeki ürünleri (detayları) ekle
                $temp_siparisler[$siparis_id]['Detaylar'][] = [
                    'UrunAdi' => $row['UrunAdi'], // Urun tablosundan
                    'Adet' => $row['Adet'],
                    'BirimFiyat' => $row['BirimFiyat']
                ];
            }
            $siparisler = $temp_siparisler;
            $stmt->close();
        } else {
            $hata = "Sipariş verileri çekilemedi: " . $stmt->error;
        }

        // MySQLi Bugfix: SP çağrısından sonra bağlantıyı temizle
        while ($conn->more_results() && $conn->next_result()) { ; }

    } else {
        $hata = "Sistem hatası (SP Hazırlama): " . $conn->error;
    }
} else {
    // Kullanıcı tablosunda var ama Müşteri tablosunda kaydı yoksa
    $hata = "Müşteri profil kaydınız bulunamadı. Lütfen yönetici ile iletişime geçin.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Müşteri Paneli</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background-color: #f9f9f9; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        .logout-btn { background-color: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; }
        .order-card { background: white; border: 1px solid #ddd; margin-bottom: 20px; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .order-header { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px; }
        .status-active { color: #28a745; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #eee; padding: 8px; text-align: left; }
        th { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>🛍️ Müşteri Paneli</h1>
            <p>Hoş Geldiniz, <strong><?php echo htmlspecialchars($ad_soyad); ?></strong></p>
        </div>
        <div>
            <a href="../logout.php" class="logout-btn">Güvenli Çıkış</a>
        </div>
    </div>

    <h2>📦 Sipariş Geçmişim</h2>

    <?php if ($hata): ?>
        <div style="background-color: #ffeeba; color: #856404; padding: 15px; border-radius: 5px;">
            ⚠️ <?php echo $hata; ?>
        </div>
    <?php elseif (empty($siparisler)): ?>
        <p>Henüz vermiş olduğunuz bir sipariş bulunmamaktadır. Alışverişe hemen başlayın!</p>
    <?php else: ?>
        
        <?php foreach ($siparisler as $siparis): ?>
            <div class="order-card">
                <div class="order-header">
                    <h3>Sipariş No: #<?php echo $siparis['SiparisID']; ?></h3>
                    <p>
                        Tarih: <?php echo date("d.m.Y", strtotime($siparis['SiparisTarihi'])); ?> | 
                        Durum: <span class="status-active"><?php echo htmlspecialchars($siparis['SiparisDurumu']); ?></span> | 
                        Toplam: <strong><?php echo number_format($siparis['ToplamTutar'], 2); ?> ₺</strong>
                    </p>
                    <p style="font-size: 0.9em; color: #666;">📍 Teslimat Adresi: <?php echo htmlspecialchars($siparis['AcikAdres']); ?></p>
                </div>
                
                <h4>Sipariş İçeriği:</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Ürün Adı</th>
                            <th>Adet</th>
                            <th>Birim Fiyat</th>
                            <th>Ara Toplam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($siparis['Detaylar'] as $detay): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detay['UrunAdi']); ?></td>
                                <td><?php echo htmlspecialchars($detay['Adet']); ?></td>
                                <td><?php echo number_format($detay['BirimFiyat'], 2); ?> ₺</td>
                                <td><?php echo number_format($detay['Adet'] * $detay['BirimFiyat'], 2); ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

</body>
</html>