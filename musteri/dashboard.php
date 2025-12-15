<?php
// /musteri/dashboard.php
session_start();

include '../db_config.php'; 

// Yetki Kontrolü: Sadece Müşteri (Rol ID: 3) girebilir.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 3) {
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$hata = '';

// Session'dan kullanıcı bilgilerini al
$kullanici_id = $_SESSION['kullanici_id']; 

// 1. Adım: KullanıcıID'den MusteriID'yi bul.
$sql_musteri = "SELECT MusteriID FROM MUSTERI WHERE KullaniciID = $kullanici_id";
$result_musteri = $conn->query($sql_musteri);

if ($result_musteri->num_rows > 0) {
    $musteri_info = $result_musteri->fetch_assoc();
    $musteri_id = $musteri_info['MusteriID'];

    // Saklı Yordam (SP) çağırılıyor
    if ($stmt = $conn->prepare("CALL SP_MusteriSiparisDetaylari(?)")) {
        $stmt->bind_param("i", $musteri_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            // Veriyi Gruplama Algoritması
            $temp_siparisler = [];
            
            while ($row = $result->fetch_assoc()) {
                $siparis_id = $row['SiparisID'];
                
                // O anki satırın (ürünün) tutarını hesapla
                $satir_tutari = $row['Adet'] * $row['BirimFiyat'];

                // Eğer bu sipariş dizide yoksa, başlık bilgilerini oluştur
                if (!isset($temp_siparisler[$siparis_id])) {
                    $temp_siparisler[$siparis_id] = [
                        'SiparisID' => $row['SiparisID'],
                        'SiparisTarihi' => $row['SiparisTarihi'],
                        'ToplamTutar' => 0, 
                        'SiparisDurumu' => $row['SiparisDurumu'],
                        'AcikAdres' => $row['AcikAdres'], 
                        'Detaylar' => [] 
                    ];
                }
                
                // Her satırın tutarını siparişin genel toplamına ekle
                $temp_siparisler[$siparis_id]['ToplamTutar'] += $satir_tutari;

                // Siparişin içindeki ürünleri (detayları) ekle
                $temp_siparisler[$siparis_id]['Detaylar'][] = [
                    'UrunAdi' => $row['UrunAdi'], 
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
    $hata = "Müşteri profil kaydınız bulunamadı. Lütfen yönetici ile iletişime geçin.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Durumum - E-Ticaret Sistemi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Başlık hizalaması için ufak bir düzenleme */
        .header {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="page-container fade-in">
        
        <?php include 'menu.php'; ?>

        <div class="header">
            <div>
                <h1>📦 Sipariş Durumum</h1>
            </div>
            </div>

        <h2 style="margin-top:20px;">Sipariş Geçmişi Listesi</h2>

        <?php if ($hata): ?>
            <div class="alert alert-error">
                <strong>⚠️ Hata:</strong> <?php echo htmlspecialchars($hata); ?>
            </div>
        <?php elseif (empty($siparisler)): ?>
            <div class="card text-center">
                <h3>Henüz Siparişiniz Yok</h3>
                <p>Henüz vermiş olduğunuz bir sipariş bulunmamaktadır.</p>
                <br>
                <a href="urunler.php" class="shop-btn" style="background-color: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">Alışverişe Başla 🛒</a>
            </div>
        <?php else: ?>
            
            <?php foreach ($siparisler as $siparis): ?>
                <div class="order-card">
                    <div class="order-header">
                        <h3>Sipariş No: #<?php echo htmlspecialchars($siparis['SiparisID']); ?></h3>
                        <div class="order-meta">
                            <span>📅 <strong>Tarih:</strong> <?php echo date("d.m.Y", strtotime($siparis['SiparisTarihi'])); ?></span>
                            <span>📊 <strong>Durum:</strong> <span class="status-active"><?php echo htmlspecialchars($siparis['SiparisDurumu']); ?></span></span>
                            <span>💰 <strong>Toplam:</strong> <?php echo number_format($siparis['ToplamTutar'], 2); ?> ₺</span>
                        </div>
                        <p style="margin-top: 0.75rem; color: var(--text-secondary);">
                            <strong>📍 Teslimat Adresi:</strong> <?php echo htmlspecialchars($siparis['AcikAdres']); ?>
                        </p>
                    </div>
                    
                    <h4 style="margin-bottom: 1rem; color: var(--text-primary);">Sipariş İçeriği</h4>
                    <div class="table-container">
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
                                        <td><strong><?php echo htmlspecialchars($detay['UrunAdi']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($detay['Adet']); ?> adet</td>
                                        <td><?php echo number_format($detay['BirimFiyat'], 2); ?> ₺</td>
                                        <td><strong><?php echo number_format($detay['Adet'] * $detay['BirimFiyat'], 2); ?> ₺</strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</body>
</html>