<?php
// /musteri/dashboard.php
session_start();

require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Yetki Kontrolü: Sadece Müşteri (Rol ID: 3) girebilir.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE || $_SESSION['rol_id'] != 3) {
    header("location: ../login.php");
    exit;
}

$siparisler = [];
$hata = '';

$kullanici_id = $_SESSION['kullanici_id']; 

// 1. Adım: KullanıcıID'den MusteriID'yi bul
$sql_musteri = "SELECT MusteriID FROM MUSTERI WHERE KullaniciID = ?";
$stmt_m = $conn->prepare($sql_musteri);
$stmt_m->execute([$kullanici_id]);
$musteri_info = $stmt_m->fetch(PDO::FETCH_ASSOC);

if ($musteri_info) {
    $musteri_id = $musteri_info['MusteriID'];

    try {
        $stmt = $conn->prepare("CALL SP_MusteriSiparisDetaylari(?)");
        $stmt->execute([$musteri_id]);
        
        $temp_siparisler = [];
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as $row) {
            $siparis_id = $row['SiparisID'];
            
            // Satırın ham tutarı (KDV hariç Ara Toplam için)
            $satir_tutari = $row['Adet'] * $row['BirimFiyat'];

            if (!isset($temp_siparisler[$siparis_id])) {
                $temp_siparisler[$siparis_id] = [
                    'SiparisID' => $row['SiparisID'],
                    'SiparisTarihi' => $row['SiparisTarihi'],
                    'AraToplam' => 0, 
                    'SiparisDurumu' => $row['SiparisDurumu'],
                    'AcikAdres' => $row['AcikAdres'], 
                    'Detaylar' => [] 
                ];
            }
            
            // Ara toplamı biriktir
            $temp_siparisler[$siparis_id]['AraToplam'] += $satir_tutari;

            $temp_siparisler[$siparis_id]['Detaylar'][] = [
                'UrunAdi' => $row['UrunAdi'], 
                'Adet' => $row['Adet'],
                'BirimFiyat' => $row['BirimFiyat']
            ];
        }
        $siparisler = $temp_siparisler;
        $stmt->closeCursor();

    } catch (PDOException $e) {
        $hata = "Sipariş verileri çekilemedi: " . $e->getMessage();
    }
} else {
    $hata = "Müşteri profil kaydınız bulunamadı.";
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
        .header { margin-bottom: 20px; }
        .order-card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); color: #333; border: 1px solid #eef2f7; }
        .order-header { border-bottom: 1px solid #f1f5f9; margin-bottom: 15px; padding-bottom: 15px; }
        .order-meta { display: flex; gap: 20px; flex-wrap: wrap; font-size: 14px; align-items: center; }
        .status-badge { background: #dbeafe; color: #2563eb; padding: 4px 12px; border-radius: 999px; font-weight: bold; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; background: #f8fafc; color: #64748b; font-size: 13px; text-transform: uppercase; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        .text-right { text-align: right; }
        .total-row td { border: none; padding: 4px 12px; color: #64748b; }
        .grand-total td { font-size: 16px; color: #1e293b; font-weight: bold; padding-top: 10px; }
        .price-text { color: #2563eb; font-weight: bold; }
    </style>
</head>
<body>
    <div class="page-container fade-in">
        
        <?php include 'menu.php'; ?>

        <div class="header">
            <h1>📦 Sipariş Durumum</h1>
        </div>

        <h2 style="margin-top:20px; color: #1e293b;">Sipariş Geçmişi Listesi</h2>

        <?php if ($hata): ?>
            <div class="alert alert-error">
                <strong>⚠️ Hata:</strong> <?php echo htmlspecialchars($hata); ?>
            </div>
        <?php elseif (empty($siparisler)): ?>
            <div class="card text-center" style="padding: 40px;">
                <h3>Henüz Siparişiniz Yok</h3>
                <p>Henüz vermiş olduğunuz bir sipariş bulunmamaktadır.</p>
                <br>
                <a href="urunler.php" class="btn" style="background-color: #2563eb; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">Alışverişe Başla 🛒</a>
            </div>
        <?php else: ?>
            
            <?php foreach ($siparisler as $siparis): 
                $ara_toplam = $siparis['AraToplam'];
                $kdv_tutari = $ara_toplam * 0.20;
                $genel_toplam = $ara_toplam + $kdv_tutari;
            ?>
                <div class="order-card">
                    <div class="order-header">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <h3 style="margin:0; color: #1e293b;">Sipariş No: #<?php echo htmlspecialchars($siparis['SiparisID']); ?></h3>
                            <span class="status-badge"><?php echo htmlspecialchars($siparis['SiparisDurumu']); ?></span>
                        </div>
                        <div class="order-meta">
                            <span>📅 <strong>Tarih:</strong> <?php echo date("d.m.Y H:i", strtotime($siparis['SiparisTarihi'])); ?></span>
                            <span>📍 <strong>Adres:</strong> <?php echo htmlspecialchars($siparis['AcikAdres']); ?></span>
                        </div>
                    </div>
                    
                    <h4 style="margin: 15px 0 10px 0; color: #64748b; font-size: 14px;">Sipariş İçeriği</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Ürün Adı</th>
                                <th>Adet</th>
                                <th>Birim Fiyat</th>
                                <th class="text-right">Satır Toplamı</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siparis['Detaylar'] as $detay): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($detay['UrunAdi']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($detay['Adet']); ?> adet</td>
                                    <td><?php echo number_format($detay['BirimFiyat'], 2, ',', '.'); ?> ₺</td>
                                    <td class="text-right"><?php echo number_format($detay['Adet'] * $detay['BirimFiyat'], 2, ',', '.'); ?> ₺</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="3" class="text-right">Ara Toplam:</td>
                                <td class="text-right"><?php echo number_format($ara_toplam, 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3" class="text-right">KDV (%20):</td>
                                <td class="text-right"><?php echo number_format($kdv_tutari, 2, ',', '.'); ?> ₺</td>
                            </tr>
                            <tr class="grand-total">
                                <td colspan="3" class="text-right">Genel Toplam:</td>
                                <td class="text-right price-text"><?php echo number_format($genel_toplam, 2, ',', '.'); ?> ₺</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>
</body>
</html>