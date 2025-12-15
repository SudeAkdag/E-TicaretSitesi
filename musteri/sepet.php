<?php
// /musteri/sepet.php
session_start();
include '../db_config.php';

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
$musteri_id_sorgu = $conn->query(
    "SELECT MusteriID FROM MUSTERI WHERE KullaniciID = " . (int)$_SESSION['kullanici_id']
);
$musteri_row = $musteri_id_sorgu->fetch_assoc();
$musteri_id = $musteri_row['MusteriID'];

// --- SİPARİŞİ TAMAMLAMA ---
if (isset($_POST['siparisi_tamamla']) && !empty($_SESSION['sepet'])) {
    $adres_sorgu = $conn->query("SELECT AdresID FROM ADRES WHERE MusteriID = $musteri_id LIMIT 1");

    if ($adres_sorgu->num_rows > 0) {
        $adres_row = $adres_sorgu->fetch_assoc();
        $adres_id = $adres_row['AdresID'];

        try {
            $conn->begin_transaction();

            // 1. Siparişi Oluştur
            $stmt = $conn->prepare("INSERT INTO SIPARIS (MusteriID, AdresID, Durum, ToplamTutar) VALUES (?, ?, 'Beklemede', 0)");
            $stmt->bind_param("ii", $musteri_id, $adres_id);
            $stmt->execute();
            $yeni_siparis_id = $conn->insert_id;

            // 2. Sipariş Detaylarını (Ürünleri) Ekle
            foreach ($_SESSION['sepet'] as $u_id => $adet) {
                $u_id  = (int)$u_id;
                $adet  = (int)$adet;
                if ($adet <= 0) continue;

                $fiyat_sor = $conn->query("SELECT Fiyat FROM URUN WHERE UrunID = $u_id");
                $fiyat_cek = $fiyat_sor->fetch_assoc();
                $birim_fiyat = $fiyat_cek['Fiyat'];

                $stmt_detay = $conn->prepare("INSERT INTO SIPARISDETAY (SiparisID, UrunID, Adet, BirimFiyat) VALUES (?, ?, ?, ?)");
                $stmt_detay->bind_param("iiid", $yeni_siparis_id, $u_id, $adet, $birim_fiyat);
                $stmt_detay->execute();
            }

            $conn->commit();
            
            // Sepeti Boşalt
            unset($_SESSION['sepet']);

            // --- DEĞİŞİKLİK BURADA: Toast Bildirimi İçin Session Ayarı ---
            $_SESSION['swal_icon'] = 'success';
            $_SESSION['swal_title'] = 'Siparişiniz Alındı!';
            $_SESSION['swal_text'] = "Siparişiniz başarıyla oluşturuldu. Sipariş No: #$yeni_siparis_id";

            // Dashboard'a yönlendir (Orada bildirim çıkacak)
            header("Location: dashboard.php");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $hata = 'Bir hata oluştu: ' . $e->getMessage();
        }
    } else {
        $hata = "Sipariş vermek için önce profilinizden bir adres eklemelisiniz.";
    }
}

// Sepet Verilerini Çekme (Görüntüleme İçin)
$result = null;
if (!empty($_SESSION['sepet'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['sepet'])));
    $sql = "SELECT * FROM URUN WHERE UrunID IN ($ids)";
    $result = $conn->query($sql);
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
            --bg: #0b1221; --card: #0f172a; --text: #e5e7eb; --muted: #9ca3af;
            --primary: #2563eb; --accent: #f97316; --border: #1f2937;
            --success: #22c55e; --danger: #f97373; --radius: 14px;
            --shadow: 0 16px 40px rgba(0,0,0,0.45);
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', system-ui, sans-serif; background: radial-gradient(circle at top, #1f2937 0, #020617 55%); color: var(--text); }
        .page { max-width: 960px; margin: 0 auto; padding: 28px 16px 56px; }
        
        .header { display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 18px; flex-wrap: wrap; }
        .title-block h1 { margin: 0; font-size: 26px; }
        .title-block p { margin: 4px 0 0; color: var(--muted); font-size: 14px; }
        
        .card { background: rgba(15,23,42,0.96); border-radius: var(--radius); border: 1px solid rgba(148,163,184,0.35); box-shadow: var(--shadow); padding: 18px 18px 20px; margin-top: 10px; }
        
        .alert { padding: 10px 12px; border-radius: 10px; margin-bottom: 14px; font-size: 14px; }
        .alert.success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.45); color: #bbf7d0; }
        .alert.error { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.45); color: #fecaca; }
        
        .table-container { margin-top: 10px; border-radius: 12px; border: 1px solid rgba(148,163,184,0.35); overflow: hidden; background: #020617; }
        table { width: 100%; border-collapse: collapse; color: var(--text); font-size: 14px; }
        thead { background: rgba(15,23,42,0.98); }
        th, td { padding: 10px 12px; text-align: left; vertical-align: middle; }
        th { font-weight: 600; color: #9ca3af; border-bottom: 1px solid rgba(55,65,81,0.9); }
        tbody tr:nth-child(even) { background: rgba(15,23,42,0.85); }
        tbody tr:nth-child(odd) { background: rgba(15,23,42,0.6); }
        tfoot td { background: rgba(15,23,42,0.95); border-top: 1px solid rgba(55,65,81,0.9); }
        tfoot strong { font-size: 15px; }
        
        .btn-checkout { margin-top: 16px; width: 100%; padding: 13px 16px; border-radius: 999px; border: none; background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 18px 40px rgba(22,163,74,0.5); color: white; font-size: 16px; font-weight: 700; cursor: pointer; }
        .btn-checkout:hover { transform: translateY(-1px); box-shadow: 0 20px 50px rgba(22,163,74,0.6); }
        
        .empty { margin-top: 18px; font-size: 14px; color: var(--muted); }

        /* --- KÜÇÜLTÜLMÜŞ INPUT ALANI --- */
        .qty-input {
            width: 32px; 
            padding: 3px 0;
            border-radius: 4px;
            border: 1px solid rgba(148,163,184,0.5);
            background: rgba(15,23,42,0.8);
            color: #e5e7eb;
            font-size: 13px;
            text-align: center;
        }
        .qty-input:focus { outline: none; border-color: #3b82f6; }

        .btn-remove-icon { display: flex; align-items: center; justify-content: center; width: 28px; height: 28px; background: transparent; border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; cursor: pointer; font-size: 18px; border-radius: 6px; transition: all 0.2s; margin: 0 auto; }
        .btn-remove-icon:hover { background: rgba(239,68,68,0.15); color: #f87171; border-color: rgba(239, 68, 68, 0.6); }
        
        .center-cell { text-align: center; }
        .right-cell { text-align: right; }
        .note { margin-top: 10px; font-size: 12px; color: var(--muted); }
    </style>
</head>
<body>
<div class="page">

    <?php include 'menu.php'; ?>

    <div class="header">
        <div class="title-block">
            <h1>🛍️ Alışveriş Sepetim</h1>
            <p>Seçtiğiniz ürünleri kontrol edip siparişinizi tamamlayın.</p>
        </div>
    </div>

    <?php if(isset($hata)): ?>
        <div class="alert error"><?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['sepet']) && isset($result) && $result->num_rows > 0): ?>
        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="40%">Ürün Adı</th>
                            <th width="15%">Birim Fiyat</th>
                            <th width="10%" class="center-cell">Adet</th>
                            <th width="20%" class="right-cell">Ara Toplam</th>
                            <th width="10%" class="center-cell">Sil</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $genel_toplam = 0;
                    while($urun = $result->fetch_assoc()):
                        $adet = $_SESSION['sepet'][$urun['UrunID']];
                        $ara_toplam = $urun['Fiyat'] * $adet;
                        $genel_toplam += $ara_toplam;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($urun['UrunAdi']); ?></td>
                            <td><?php echo number_format($urun['Fiyat'], 2); ?> ₺</td>
                            
                            <td class="center-cell">
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="urun_id" value="<?php echo (int)$urun['UrunID']; ?>">
                                    <input type="hidden" name="adet_guncelle" value="1">
                                    <input type="number" name="adet" value="<?php echo (int)$adet; ?>" min="1" class="qty-input" onchange="this.form.submit()">
                                </form>
                            </td>

                            <td class="right-cell">
                                <strong><?php echo number_format($ara_toplam, 2); ?> ₺</strong>
                            </td>

                            <td class="center-cell">
                                <form method="post" style="margin:0;">
                                    <input type="hidden" name="urun_id" value="<?php echo (int)$urun['UrunID']; ?>">
                                    <button type="submit" name="kaldir" class="btn-remove-icon" title="Kaldır">×</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;"><strong>GENEL TOPLAM:</strong></td>
                            <td class="right-cell"><strong><?php echo number_format($genel_toplam, 2); ?> ₺</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <form method="post">
                <p class="note">* Miktarı değiştirdiğinizde sepet otomatik güncellenir.</p>
                <button type="submit" name="siparisi_tamamla" class="btn-checkout">✅ Siparişi Tamamla</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card empty">Sepetiniz boş. Ürünler sayfasına giderek ürün ekleyebilirsiniz.</div>
    <?php endif; ?>

</div>
</body>
</html>