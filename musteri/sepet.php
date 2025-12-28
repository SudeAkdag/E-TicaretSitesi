<?php
// /musteri/sepet.php
session_start();
require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}

// Yetki Kontrolü
if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 3) {
    header("location: ../login.php");
    exit;
}

// --- PHP İŞLEMLERİ (Sepet Güncelleme/Silme) ---
if (!empty($_POST['urun_id'])) {
    $uid = (int)$_POST['urun_id'];

    if (isset($_POST['adet_guncelle'])) {
        $yeni_adet = (int)$_POST['adet'];
        if ($yeni_adet > 0) {
            $_SESSION['sepet'][$uid] = $yeni_adet;
        } else {
            unset($_SESSION['sepet'][$uid]); 
        }
    } 
    elseif (isset($_POST['kaldir'])) {
        unset($_SESSION['sepet'][$uid]);
    }
}

// Müşteri ID Bulma
$stmt_m = $conn->prepare("SELECT MusteriID FROM MUSTERI WHERE KullaniciID = ?");
$stmt_m->execute([(int)$_SESSION['kullanici_id']]);
$musteri_row = $stmt_m->fetch(PDO::FETCH_ASSOC);
$musteri_id = $musteri_row ? $musteri_row['MusteriID'] : null;

// --- SİPARİŞİ TAMAMLAMA ---
if (isset($_POST['siparisi_tamamla']) && !empty($_SESSION['sepet']) && $musteri_id) {
    
    // 1. Önce Mevcut Sepetin KDV'li Toplam Tutarı Hesapla
    $ara_toplam_hesapla = 0;
    foreach ($_SESSION['sepet'] as $u_id => $adet) {
        $stmt_f = $conn->prepare("SELECT Fiyat FROM URUN WHERE UrunID = ?");
        $stmt_f->execute([(int)$u_id]);
        $f = $stmt_f->fetch(PDO::FETCH_ASSOC);
        if ($f) {
            $ara_toplam_hesapla += ($f['Fiyat'] * $adet);
        }
    }
    $kdv_orani = 0.20;
    $net_toplam_tutar = $ara_toplam_hesapla * (1 + $kdv_orani); // Örn: 1.800 * 1.20 = 2.160

    // 2. Adres Kontrolü
    $stmt_a = $conn->prepare("SELECT AdresID FROM ADRES WHERE MusteriID = ? LIMIT 1");
    $stmt_a->execute([$musteri_id]);
    $adres_row = $stmt_a->fetch(PDO::FETCH_ASSOC);

    if ($adres_row) {
        $adres_id = $adres_row['AdresID'];

        try {
            $conn->beginTransaction();

            // 3. Siparişi Oluştur (KDV Dahil Net Tutarla)
            $stmt = $conn->prepare("INSERT INTO SIPARIS (MusteriID, AdresID, Durum, ToplamTutar) VALUES (?, ?, 'Beklemede', ?)");
            $stmt->execute([$musteri_id, $adres_id, $net_toplam_tutar]);
            $yeni_siparis_id = $conn->lastInsertId();

            // 4. Sipariş Detaylarını Ekle
            foreach ($_SESSION['sepet'] as $u_id => $adet) {
                $u_id  = (int)$u_id;
                $adet  = (int)$adet;
                if ($adet <= 0) continue;

                $stmt_f = $conn->prepare("SELECT Fiyat FROM URUN WHERE UrunID = ?");
                $stmt_f->execute([$u_id]);
                $fiyat_cek = $stmt_f->fetch(PDO::FETCH_ASSOC);
                $birim_fiyat = $fiyat_cek['Fiyat'];

                $stmt_detay = $conn->prepare("INSERT INTO SIPARISDETAY (SiparisID, UrunID, Adet, BirimFiyat) VALUES (?, ?, ?, ?)");
                $stmt_detay->execute([$yeni_siparis_id, $u_id, $adet, $birim_fiyat]);
            }

            $conn->commit();
            unset($_SESSION['sepet']);

            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Siparişiniz Alındı!';
            $_SESSION['swal_text'] = "Siparişiniz başarıyla oluşturuldu. Sipariş No: #$yeni_siparis_id";

            header("Location: dashboard.php");
            exit;

        } catch (Exception $e) {
            $conn->rollBack();
            $hata = 'Bir hata oluştu: ' . $e->getMessage();
        }
    } else {
        $hata = "Sipariş vermek için önce profilinizden bir adres eklemelisiniz.";
    }
}

// Sepet Verilerini Çekme
$urunler = [];
if (!empty($_SESSION['sepet'])) {
    $ids = array_keys($_SESSION['sepet']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT * FROM URUN WHERE UrunID IN ($placeholders)";
    $stmt_sepet = $conn->prepare($sql);
    $stmt_sepet->execute(array_values($ids));
    $urunler = $stmt_sepet->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sepetim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --bg: #0b1221; --card: #0f172a; --text: #f8fafc; --muted: #9ca3af;
            --primary: #2563eb; --accent: #f97316; --border: #1f2937;
            --success: #22c55e; --danger: #f97373; --radius: 14px;
            --shadow: 0 16px 40px rgba(0,0,0,0.45);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: radial-gradient(circle at top, #1f2937 0, #020617 55%); color: var(--text); }
        .page { max-width: 960px; margin: 0 auto; padding: 28px 16px 56px; }
        .header { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 18px; flex-wrap: wrap; }
        .card { background: rgba(15,23,42,0.96); border-radius: var(--radius); border: 1px solid rgba(148,163,184,0.35); box-shadow: var(--shadow); padding: 18px 18px 20px; margin-top: 10px; }
        .alert { padding: 10px 12px; border-radius: 10px; margin-bottom: 14px; font-size: 14px; }
        .alert.error { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.45); color: #fecaca; }
        .table-container { margin-top: 10px; border-radius: 12px; border: 1px solid rgba(148,163,184,0.35); overflow: hidden; background: #020617; }
        table { width: 100%; border-collapse: collapse; color: #060606ff; font-size: 14px; }
        th, td { padding: 12px 15px; text-align: left; }
        th { background: #1e293b; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 12px; }
        td { border-bottom: 1px solid rgba(55,65,81,0.5); }
        .btn-checkout { margin-top: 16px; width: 100%; padding: 13px 16px; border-radius: 999px; border: none; background: linear-gradient(135deg, #22c55e, #16a34a); color: white; font-size: 16px; font-weight: 700; cursor: pointer; transition: transform 0.2s; }
        .btn-checkout:hover { transform: scale(1.01); }
        .qty-input { width: 45px; padding: 5px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: #fff; text-align: center; }
        .btn-remove-icon { background: transparent; border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; cursor: pointer; font-size: 18px; border-radius: 6px; padding: 0 8px; }
    </style>
</head>
<body>
<div class="page">
    <?php include 'menu.php'; ?>

    <div class="header">
        <div class="title-block">
            <h1>🛍️ Alışveriş Sepetim</h1>
            <p style="color:var(--muted)">Seçtiğiniz ürünleri kontrol edip siparişinizi tamamlayın.</p>
        </div>
    </div>

    <?php if(isset($hata)): ?>
        <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>

    <?php if (!empty($urunler)): ?>
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="40%">Ürün Adı</th>
                            <th width="15%">Birim Fiyat</th>
                            <th width="10%" style="text-align:center">Adet</th>
                            <th width="20%" style="text-align:right">Ara Toplam</th>
                            <th width="10%" style="text-align:center">Sil</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $genel_toplam = 0;
                    foreach($urunler as $urun):
                        $adet = $_SESSION['sepet'][$urun['UrunID']];
                        $ara_toplam = $urun['Fiyat'] * $adet;
                        $genel_toplam += $ara_toplam;
                    ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($urun['UrunAdi']); ?></strong></td>
                            <td><?php echo number_format($urun['Fiyat'], 2); ?> ₺</td>
                            <td style="text-align:center">
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="urun_id" value="<?php echo (int)$urun['UrunID']; ?>">
                                    <input type="hidden" name="adet_guncelle" value="1">
                                    <input type="number" name="adet" value="<?php echo (int)$adet; ?>" min="1" class="qty-input" onchange="this.form.submit()">
                                </form>
                            </td>
                            <td style="text-align:right">
                                <strong><?php echo number_format($ara_toplam, 2); ?> ₺</strong>
                            </td>
                            <td style="text-align:center">
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="urun_id" value="<?php echo (int)$urun['UrunID']; ?>">
                                    <button type="submit" name="kaldir" class="btn-remove-icon">×</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <?php 
                        $kdv_tutari = $genel_toplam * 0.20; 
                        $kdv_dahil_net_toplam = $genel_toplam + $kdv_tutari;
                        ?>
                        <tr>
                            <td colspan="3" style="text-align:right; color: #030303ff; border:none; padding-top:20px;">Ara Toplam:</td>
                            <td style="text-align:right; border:none; padding-top:20px;"><?php echo number_format($genel_toplam, 2); ?> ₺</td>
                            <td style="border:none;"></td>
                        </tr>
                        <tr>
                            <td colspan="3" style="text-align:right; color: #0c0c0cff; border:none;">KDV (%20):</td>
                            <td style="text-align:right; border:none;"><?php echo number_format($kdv_tutari, 2); ?> ₺</td>
                            <td style="border:none;"></td>
                        </tr>
                        <tr style="background: rgba(37, 99, 235, 0.1);">
                            <td colspan="3" style="text-align:right; border:none;"><strong>GENEL TOPLAM:</strong></td>
                            <td style="text-align:right; color: #3b82f6; font-size: 1.2em; border:none;">
                                <strong><?php echo number_format($kdv_dahil_net_toplam, 2); ?> ₺</strong>
                            </td>
                            <td style="border:none;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <form method="post">
                <p style="font-size:12px; color:var(--muted); margin-top:10px;">* Miktarı değiştirdiğinizde sepet otomatik güncellenir.</p>
                <button type="submit" name="siparisi_tamamla" class="btn-checkout">✅ Siparişi Tamamla</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card" style="color:var(--muted); text-align:center; padding: 50px;">
            <h3>Sepetiniz şu an boş 🛒</h3>
            <p>Ürünler sayfasına giderek alışverişe başlayabilirsiniz.</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>