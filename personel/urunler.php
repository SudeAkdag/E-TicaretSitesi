<?php
// /personel/urunler.php
session_start();
include '../db_config.php';

// Yetki Kontrolü (Personel RolID: 2)
if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php"); exit;
}

// --- 1. ADIM: Giriş Yapan Personelin ID'sini Bul ---
// Çünkü stok hareketini kimin yaptığını kaydetmemiz lazım.
$kullanici_id = $_SESSION['kullanici_id'];
$p_sor = $conn->query("SELECT PersonelID FROM PERSONEL WHERE KullaniciID = $kullanici_id");
$p_row = $p_sor->fetch_assoc();
$aktif_personel_id = $p_row['PersonelID'];

$mesaj = null;
$hata = null;

// -- ÜRÜN GÜNCELLEME İŞLEMİ --
if (isset($_POST['guncelle'])) {
    $uid = $_POST['urun_id'];
    $fiyat = $_POST['fiyat'];
    $yeni_stok = $_POST['stok'];

    // Önce eski stoğu çekelim (Hareket kaydı için farkı bulacağız)
    $eski_stok_sor = $conn->query("SELECT StokAdedi FROM URUN WHERE UrunID = $uid");
    $eski_stok_row = $eski_stok_sor->fetch_assoc();
    $eski_stok = $eski_stok_row['StokAdedi'];
    
    $stmt = $conn->prepare("UPDATE URUN SET Fiyat=?, StokAdedi=? WHERE UrunID=?");
    $stmt->bind_param("dii", $fiyat, $yeni_stok, $uid);
    
    if($stmt->execute()) {
        $mesaj = "Ürün başarıyla güncellendi.";

        // --- STOK HAREKETİ KAYDI (GÜNCELLEME) ---
        // Eğer stok değiştiyse hareket tablosuna yaz
        if ($yeni_stok != $eski_stok) {
            $fark = $yeni_stok - $eski_stok;
            $tur = ($fark > 0) ? 'Giris' : 'Cikis'; // Arttıysa giriş, azaldıysa çıkış
            $miktar = abs($fark); // Mutlak değer (pozitif sayı)

            $stmt_har = $conn->prepare("INSERT INTO STOKHAREKETI (UrunID, PersonelID, Miktar, HareketTuru) VALUES (?, ?, ?, ?)");
            $stmt_har->bind_param("iiis", $uid, $aktif_personel_id, $miktar, $tur);
            $stmt_har->execute();
        }
    } else {
        $hata = "Güncelleme başarısız oldu.";
    }
}

// -- YENİ ÜRÜN VE TEDARİKÇİ EKLEME İŞLEMİ --
if (isset($_POST['yeni_ekle'])) {
    $ad = trim($_POST['yeni_ad']);
    $fiyat = $_POST['yeni_fiyat'];
    $stok = $_POST['yeni_stok'];
    $kat = $_POST['yeni_kategori'];
    $secilen_tedarikci = $_POST['mevcut_tedarikci']; 
    
    $yeni_ted_ad = trim($_POST['ted_ad']);
    $yeni_ted_soyad = trim($_POST['ted_soyad']);
    $yeni_ted_email = trim($_POST['ted_email']);
    $yeni_ted_tel = trim($_POST['ted_tel']);

    $son_tedarikci_id = 0;
    
    // İsim Kontrolü
    $kontrol = $conn->prepare("SELECT UrunID FROM URUN WHERE UrunAdi = ?");
    $kontrol->bind_param("s", $ad);
    $kontrol->execute();
    $kontrol->store_result();

    if ($kontrol->num_rows > 0) {
        $hata = "Bu ürün ismi ('$ad') zaten kayıtlı!";
    } else {
        $islem_tamam = true;

        // Tedarikçi Mantığı
        if (!empty($yeni_ted_ad) && !empty($yeni_ted_email)) {
            $ted_kontrol = $conn->prepare("SELECT TedarikciID FROM TEDARIKCI WHERE Email = ?");
            $ted_kontrol->bind_param("s", $yeni_ted_email);
            $ted_kontrol->execute();
            
            if ($ted_kontrol->get_result()->num_rows > 0) {
                $hata = "Bu E-Posta adresiyle kayıtlı bir tedarikçi zaten var!";
                $islem_tamam = false;
            } else {
                $stmt_ted = $conn->prepare("INSERT INTO TEDARIKCI (TedarikciAdi, TedarikciSoyadi, Email, Telefon) VALUES (?, ?, ?, ?)");
                $stmt_ted->bind_param("ssss", $yeni_ted_ad, $yeni_ted_soyad, $yeni_ted_email, $yeni_ted_tel);
                if ($stmt_ted->execute()) {
                    $son_tedarikci_id = $conn->insert_id;
                } else {
                    $hata = "Tedarikçi eklenirken hata: " . $stmt_ted->error;
                    $islem_tamam = false;
                }
            }
        } elseif (!empty($secilen_tedarikci)) {
            $son_tedarikci_id = $secilen_tedarikci;
        } else {
            $hata = "Lütfen bir tedarikçi seçin veya yeni oluşturun.";
            $islem_tamam = false;
        }

        if ($islem_tamam && $son_tedarikci_id > 0) {
            
            // 1. Ürünü Ekle
            $stmt_urun = $conn->prepare("INSERT INTO URUN (UrunAdi, Fiyat, StokAdedi, KategoriID) VALUES (?, ?, ?, ?)");
            $stmt_urun->bind_param("sdii", $ad, $fiyat, $stok, $kat);
            
            if ($stmt_urun->execute()) {
                $yeni_urun_id = $conn->insert_id;

                // 2. İlişkiyi Kur
                $stmt_rel = $conn->prepare("INSERT INTO URUNTEDARIKCI (UrunID, TedarikciID) VALUES (?, ?)");
                $stmt_rel->bind_param("ii", $yeni_urun_id, $son_tedarikci_id);
                $stmt_rel->execute();

                // --- 3. ADIM: STOK HAREKETİ KAYDI (YENİ EKLENDİ) ---
                // Ürün ilk kez eklendiğinde 'Giris' olarak kaydediyoruz.
                if ($stok > 0) {
                    $stmt_har = $conn->prepare("INSERT INTO STOKHAREKETI (UrunID, PersonelID, Miktar, HareketTuru) VALUES (?, ?, ?, 'Giris')");
                    $stmt_har->bind_param("iii", $yeni_urun_id, $aktif_personel_id, $stok);
                    $stmt_har->execute();
                }

                $mesaj = "Ürün eklendi ve stok hareketlerine işlendi.";
            } else {
                $hata = "Ürün kaydedilemedi: " . $stmt_urun->error;
            }
        }
    }
}

// Listeleme Sorguları
$urunler = $conn->query("SELECT U.*, K.KategoriAdi FROM URUN U LEFT JOIN KATEGORI K ON U.KategoriID = K.KategoriID ORDER BY U.UrunID DESC");
$kategoriler = $conn->query("SELECT * FROM KATEGORI");
$tedarikciler = $conn->query("SELECT * FROM TEDARIKCI");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Yönetimi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .input-mini {
            background: rgba(0,0,0,0.2); border: 1px solid #334155;
            color: white; padding: 5px; border-radius: 4px; width: 80px;
        }
        .btn-update {
            background: #3b82f6; color: white; border: none;
            padding: 5px 10px; border-radius: 4px; cursor: pointer;
        }
        .new-product-form {
            background: #1e293b; padding: 25px; border-radius: 12px;
            margin-bottom: 25px; border: 1px solid #334155;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .form-row {
            display: flex; gap: 15px; width: 100%; margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .form-section-title {
            color: #94a3b8; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 10px; border-bottom: 1px solid #334155; padding-bottom: 5px; width: 100%;
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; color: #cbd5e1; font-weight: 500; }
        .form-input { 
            padding: 0 12px; height: 40px; border-radius: 6px; 
            border: 1px solid #475569; background: #0f172a; 
            color: white; width: 100%; box-sizing: border-box;
            transition: all 0.2s;
        }
        .form-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); }
        .divider {
            display: flex; align-items: center; text-align: center; color: #64748b; margin: 15px 0; font-size: 13px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; border-bottom: 1px solid #334155;
        }
        .divider:not(:empty)::before { margin-right: .5em; }
        .divider:not(:empty)::after { margin-left: .5em; }
        .btn-add {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white; border: none; border-radius: 6px;
            height: 40px; padding: 0 30px; font-weight: bold;
            cursor: pointer; width: 100%; margin-top: 10px;
            transition: transform 0.1s;
        }
        .btn-add:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3); }
    </style>
</head>
<body>
    <div class="page-container">
        <?php include 'menu.php'; ?>
        
        <div class="new-product-form">
            <h3 style="margin-top:0; margin-bottom:20px; color: white;"> Yeni Ürün ve Tedarikçi Ekle</h3>
            
            <form method="post">
                <div class="form-section-title">📦 Ürün Bilgileri</div>
                <div class="form-row">
                    <div class="form-group" style="flex: 3; min-width: 200px;">
                        <label>Ürün Adı *</label>
                        <input type="text" name="yeni_ad" class="form-input" required placeholder="Örn: Kablosuz Mouse">
                    </div>
                    <div class="form-group" style="flex: 2; min-width: 150px;">
                        <label>Kategori *</label>
                        <select name="yeni_kategori" class="form-input" required>
                            <option value="" disabled selected>Seçiniz</option>
                            <?php foreach($kategoriler as $kat): ?>
                                <option value="<?php echo $kat['KategoriID']; ?>"><?php echo $kat['KategoriAdi']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 100px;">
                        <label>Fiyat (₺) *</label>
                        <input type="number" step="0.01" name="yeni_fiyat" class="form-input" required placeholder="0.00">
                    </div>
                    <div class="form-group" style="flex: 1; min-width: 80px;">
                        <label>Stok *</label>
                        <input type="number" name="yeni_stok" class="form-input" required placeholder="0">
                    </div>
                </div>

                <div class="form-section-title" style="margin-top: 10px;">🚚 Tedarikçi Bilgileri</div>
                
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Mevcut Tedarikçiden Seç</label>
                        <select name="mevcut_tedarikci" class="form-input" id="existingSupplier">
                            <option value="">Seçiniz</option>
                            <?php foreach($tedarikciler as $ted): ?>
                                <option value="<?php echo $ted['TedarikciID']; ?>"><?php echo $ted['TedarikciAdi']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="divider">VEYA (Yeni Tedarikçi Bilgileri Girin)</div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Şirket Adı (Ad)</label>
                        <input type="text" name="ted_ad" class="form-input new-supp" placeholder="Yeni Şirket Adı">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Yetkili Soyadı</label>
                        <input type="text" name="ted_soyad" class="form-input new-supp" placeholder="Yetkili Soyadı">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>E-Posta</label>
                        <input type="email" name="ted_email" class="form-input new-supp" placeholder="ornek@sirket.com">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Telefon</label>
                        <input type="text" name="ted_tel" class="form-input new-supp" placeholder="0212...">
                    </div>
                </div>

                <button type="submit" name="yeni_ekle" class="btn-add">✅ Kaydet ve İlişkilendir</button>
            </form>
        </div>

        <div class="card">
            <h3>📦 Mevcut Ürün Listesi</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ürün Adı</th>
                        <th>Kategori</th>
                        <th>Fiyat (₺)</th>
                        <th>Stok</th>
                        <th>İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $urunler->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['UrunID']; ?></td>
                        <td><?php echo $row['UrunAdi']; ?></td>
                        <td><span class="status-active" style="background:#334155; color:#cbd5e1;"><?php echo $row['KategoriAdi']; ?></span></td>
                        
                        <form method="post">
                            <input type="hidden" name="urun_id" value="<?php echo $row['UrunID']; ?>">
                            <td>
                                <input type="number" step="0.01" name="fiyat" value="<?php echo $row['Fiyat']; ?>" class="input-mini">
                            </td>
                            <td>
                                <input type="number" name="stok" value="<?php echo $row['StokAdedi']; ?>" class="input-mini" 
                                       style="<?php echo $row['StokAdedi'] < 10 ? 'border-color:red; color:#fca5a5;' : ''; ?>">
                            </td>
                            <td>
                                <button type="submit" name="guncelle" class="btn-update">Kaydet</button>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        const selectBox = document.getElementById('existingSupplier');
        const newInputs = document.querySelectorAll('.new-supp');

        selectBox.addEventListener('change', function() {
            if(this.value !== "") {
                newInputs.forEach(input => {
                    input.value = "";
                    input.style.opacity = "0.5";
                });
            } else {
                newInputs.forEach(input => input.style.opacity = "1");
            }
        });

        newInputs.forEach(input => {
            input.addEventListener('input', function() {
                if(this.value !== "") selectBox.value = "";
                newInputs.forEach(inp => inp.style.opacity = "1");
            });
        });
    </script>

    <?php if(isset($mesaj)): ?>
    <script>
        Toast.fire({ icon: 'success', title: 'Başarılı!', text: '<?php echo $mesaj; ?>' });
    </script>
    <?php endif; ?>

    <?php if(isset($hata)): ?>
    <script>
        Toast.fire({ icon: 'error', title: 'Hata!', text: '<?php echo $hata; ?>' });
    </script>
    <?php endif; ?>

</body>
</html>