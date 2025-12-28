<?php
// /personel/hareketlerim.php
session_start();
require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Bağlantı hatası: " . $e->getMessage());
}

// 1. Yetki Kontrolü: Sadece Personel (Rol ID: 2) girebilir.
if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php");
    exit;
}

$hareketler = [];
$hata = "";

// 2. Kullanıcı ID'den Personel ID'yi Bulma
$kullanici_id = $_SESSION['kullanici_id'];
$personel_id = 0;

try {
    // PDO Hazırlanmış İfadesi
    $stmt_pid = $conn->prepare("SELECT PersonelID FROM PERSONEL WHERE KullaniciID = ?");
    $stmt_pid->execute([$kullanici_id]); // Parametreyi burada gönderiyoruz
    $row_pid = $stmt_pid->fetch(PDO::FETCH_ASSOC); // Veriyi çekiyoruz
    
    if ($row_pid) {
        $personel_id = $row_pid['PersonelID'];
    } else {
        $hata = "Personel kaydı bulunamadı! Lütfen yöneticiyle görüşün.";
    }
} catch (PDOException $e) {
    $hata = "Sistem hatası: " . $e->getMessage();
}

// 3. Stored Procedure Çağırma (Eğer Personel ID bulunduysa)
if ($personel_id > 0) {
    try {
        // SP_PersonelStokHareketleri(PersonelID)
        $stmt = $conn->prepare("CALL SP_PersonelStokHareketleri(?)");
        if ($stmt->execute([$personel_id])) {
            // PDO'da get_result() yerine fetchAll() kullanılır
            $hareketler = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $stmt->closeCursor(); // Procedure sonrası bağlantıyı temizleme
    } catch (PDOException $e) {
        $hata = "Veriler çekilemedi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Stok Hareketlerim</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Hareket Türüne Göre Renklendirme */
        .badge-giris {
            background-color: #dcfce7; color: #166534; /* Yeşil */
            padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 13px;
            border: 1px solid #bbf7d0;
        }
        .badge-cikis {
            background-color: #fee2e2; color: #991b1b; /* Kırmızı */
            padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 13px;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
<div class="page-container fade-in">
    
    <?php include 'menu.php'; ?>
    
    <div class="header">
        <h1> Geçmiş Stok İşlemlerim</h1>
    </div>

    <?php if($hata): ?>
        <div class="alert alert-error">⚠️ <?php echo htmlspecialchars($hata); ?></div>
    <?php endif; ?>

    <div class="card">
        <?php if(empty($hareketler)): ?>
            <div style="text-align: center; padding: 20px; color: #94a3b8;">
                <h3>Henüz kayıtlı bir stok hareketiniz yok.</h3>
                <p>Ürünler sayfasından stok güncellediğinizde veya yeni ürün eklediğinizde burada görünecektir.</p>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Ürün Adı</th>
                            <th>Hareket Türü</th>
                            <th>Miktar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($hareketler as $h): 
                            // Hareket türünü belirleyelim
                            $tur = mb_strtolower($h['HareketTuru'], 'UTF-8');
                            $is_giris = ($tur == 'giris' || $tur == 'giriş');
                            $badge_class = $is_giris ? 'badge-giris' : 'badge-cikis';
                            $icon = $is_giris ? '📥' : '📤';
                        ?>
                        <tr>
                            <td>
                                📅 <?php echo date("d.m.Y H:i", strtotime($h['Tarih'])); ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($h['UrunAdi']); ?></strong>
                            </td>
                            <td>
                                <span class="<?php echo $badge_class; ?>">
                                    <?php echo $icon . ' ' . htmlspecialchars($h['HareketTuru']); ?>
                                </span>
                            </td>
                            <td style="font-weight: bold;">
                                <?php echo abs($h['Miktar']); ?> Adet
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>