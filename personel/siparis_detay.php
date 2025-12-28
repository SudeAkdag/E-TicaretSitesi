<?php
// /personel/siparis_detay.php
session_start();
require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// 1. Yetki Kontrolü: Personel (2) veya Yönetici (1) erişebilir.
if (!isset($_SESSION['loggedin']) || ($_SESSION['rol_id'] != 2 && $_SESSION['rol_id'] != 1)) {
    header("location: ../login.php"); exit;
}

$siparis_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$detaylar = [];
$toplam_tutar = 0;
$bilgi = "";
$tarih = "";
$hata = "";

// 2. Verileri Çek (PDO Yöntemi)
if ($siparis_id > 0) {
    try {
        $stmt = $conn->prepare("CALL SP_SiparisFaturaDetayi(?)");
        
        // PDO'da bind_param yerine execute içine dizi gönderilir
        if ($stmt->execute([$siparis_id])) {
            
            // fetchAll(PDO::FETCH_ASSOC) ile tüm sonuçları bir kerede diziye alıyoruz
            $detaylar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($detaylar)) {
                foreach ($detaylar as $row) {
                    // Müşteri ve Sipariş genel bilgilerini ilk satırdan alıyoruz
                    if ($bilgi == "") {
                        $bilgi = "<strong>" . htmlspecialchars($row['MusteriTamAd']) . "</strong><br>" . 
                                 "<span style='color:#666;'>" . htmlspecialchars($row['AcikAdres']) . "</span>";
                        $tarih = date("d.m.Y H:i", strtotime($row['SiparisTarihi']));
                    }
                    
                    // Toplam tutarı topluyoruz
                    $toplam_tutar += $row['SatirToplami'];
                    // ... döngü bittikten sonra (yere dikkat: foreach dışına)
                  $kdv_orani = 0.20; // %20 KDV
                  $kdv_tutari = $toplam_tutar * $kdv_orani;
                $genel_toplam_kdvli = $toplam_tutar + $kdv_tutari;
                }
            }
            
            $stmt->closeCursor(); // Procedure sonrası bağlantıyı serbest bırak
        }
    } catch (PDOException $e) {
        $hata = "Veritabanı hatası: " . $e->getMessage();
    }
} else {
    $hata = "Geçersiz Sipariş ID.";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sipariş Detayı #<?php echo $siparis_id; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .invoice-box {
            background: #fff;
            color: #333;
            padding: 40px;
            border-radius: 8px;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
        }
        .customer-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-table th {
            text-align: left;
            padding: 12px;
            background-color: #f1f5f9;
            color: #000;
            font-weight: 700;
            border-bottom: 2px solid #000;
        }
        .invoice-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 1.2rem;
        }
        .total-amount {
            color: #2563eb;
            font-weight: bold;
            font-size: 1.5rem;
        }
        @media print {
            body { background: white; margin: 0; }
            .no-print { display: none !important; }
            .invoice-box { box-shadow: none; border: none; margin: 0; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
<div class="page-container">

    <div class="no-print" style="max-width: 800px; margin: 0 auto 20px auto; display:flex; justify-content:space-between; align-items:center;">
        <a href="dashboard.php" class="btn" style="background: #64748b; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px;">
            🔙 Listeye Dön
        </a>
        <button onclick="window.print()" class="btn" style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;">
            🖨️ Yazdır / Fiş Çıkar
        </button>
    </div>

    <?php if ($hata): ?>
        <div class="alert alert-error" style="max-width: 800px; margin: 20px auto; background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px;">
            ⚠️ <?php echo htmlspecialchars($hata); ?>
        </div>
    <?php elseif (empty($detaylar)): ?>
        <div class="card" style="text-align: center; padding: 40px; background: white; border-radius: 8px; max-width: 800px; margin: 20px auto;">
            <h3>Sipariş Bulunamadı</h3>
            <p>Aradığınız numaraya ait sipariş verisi yok.</p>
        </div>
    <?php else: ?>

        <div class="invoice-box">
            
            <div class="invoice-header">
                <div>
                    <h1 style="margin: 0; color: #000; font-size: 24px;">🧾 Sipariş Fişi</h1>
                    <div style="color: #64748b; margin-top: 5px;">Sipariş No: <strong>#<?php echo $siparis_id; ?></strong></div>
                </div>
                <div class="text-right">
                    <div style="font-size: 14px; color: #64748b;">Sipariş Tarihi</div>
                    <div style="font-weight: bold; font-size: 16px; color:#000;"><?php echo $tarih; ?></div>
                </div>
            </div>

            <div class="customer-section">
                <div style="font-size: 12px; text-transform: uppercase; color: #000; font-weight: bold; margin-bottom: 5px;">
                    👤 Müşteri & Teslimat Bilgileri
                </div>
                <div style="color: #000;"><?php echo $bilgi; ?></div>
            </div>

            <table class="invoice-table">
                <thead>
                    <tr>
                        <th width="45%">Ürün Adı</th>
                        <th width="20%" class="text-center">Birim Fiyat</th>
                        <th width="15%" class="text-center">Adet</th>
                        <th width="20%" class="text-right">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($detaylar as $d): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($d['UrunAdi'] ?? 'Bilinmeyen Ürün'); ?></strong>
                        </td>
                        <td class="text-center">
                            <?php echo number_format($d['BirimFiyat'] ?? 0, 2); ?> ₺
                        </td>
                        <td class="text-center">
                            <?php echo $d['Adet'] ?? 0; ?>
                        </td>
                        <td class="text-right" style="font-weight: 500;">
                            <?php echo number_format($d['SatirToplami'] ?? 0, 2); ?> ₺
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

           <div class="total-section">
    <table style="width: 250px; margin-left: auto; font-size: 1rem;">
        <tr>
            <td class="text-right" style="color: #64748b;">Ara Toplam:</td>
            <td class="text-right"><?php echo number_format($toplam_tutar, 2, ',', '.'); ?> ₺</td>
        </tr>
        <tr>
            <td class="text-right" style="color: #64748b;">KDV (%20):</td>
            <td class="text-right"><?php echo number_format($kdv_tutari, 2, ',', '.'); ?> ₺</td>
        </tr>
        <tr>
            <td class="text-right" style="font-weight: bold; padding-top: 10px; border-top: 1px solid #e2e8f0;">Genel Toplam:</td>
            <td class="text-right total-amount" style="padding-top: 10px; border-top: 1px solid #e2e8f0;">
                <?php echo number_format($genel_toplam_kdvli, 2, ',', '.'); ?> ₺
            </td>
        </tr>
    </table>
</div>

        </div>

    <?php endif; ?>

</div>
</body>
</html>