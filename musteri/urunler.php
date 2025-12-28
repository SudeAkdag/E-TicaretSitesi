<?php
// musteri/urunler.php
session_start();
require_once '../Database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Yetki Kontrolü
if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 3) {
    header("location: ../login.php"); exit;
}

$ad_soyad = isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Değerli Müşterimiz';

// Sepete Ekleme İşlemi
if (isset($_POST['sepete_ekle'])) {
    $urun_id = (int)$_POST['urun_id'];
    $adet    = max(1, (int)$_POST['adet']);
    $_SESSION['sepet'] = $_SESSION['sepet'] ?? [];
    $_SESSION['sepet'][$urun_id] = ($_SESSION['sepet'][$urun_id] ?? 0) + $adet;
    $mesaj = "Ürün sepete eklendi!";
}

// Ürünleri Çekme (PDO Yöntemi)
$sql = "SELECT U.*, K.KategoriAdi
        FROM URUN U
        JOIN KATEGORI K ON U.KategoriID = K.KategoriID
        WHERE U.StokAdedi > 0
        ORDER BY U.UrunAdi ASC";

// query() sonucu PDOStatement döner
$stmt = $conn->query($sql);
// Tüm ürünleri bir diziye alıyoruz
$urunler = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürünler</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --bg: #0b1221; --card: #c4c8d1ff; --muted: #94a3b8;
            --text: #e2e8f0; --primary: #2563eb; --accent: #f97316;
            --success: #22c55e; --radius: 14px; --shadow: 0 12px 40px rgba(0,0,0,0.35);
        }
        body { margin:0; background:var(--bg); color:var(--text); font-family:'Inter', system-ui, -apple-system, sans-serif; }
        .page { max-width:1200px; margin:0 auto; padding:32px 20px 64px; }
        
        .welcome { background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08); border-radius:var(--radius); padding:14px 16px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .welcome h2 { margin:0; font-size:18px; color:#fff; }
        .welcome small { color:var(--muted); }

        .topbar { display:flex; align-items:center; gap:16px; justify-content:space-between; flex-wrap:wrap; margin-top:16px; }
        .pill { background:rgba(255,255,255,0.06); padding:8px 14px; border-radius:999px; color:var(--muted); font-size: 14px; }
        
        .grid { margin-top:20px; display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:18px; }
        .card { background:var(--card); border:1px solid rgba(255,255,255,0.06); border-radius:var(--radius); padding:18px; box-shadow:var(--shadow); display:flex; flex-direction:column; gap:10px; }
        .category { display:inline-flex; align-items:center; gap:6px; color:white; background:linear-gradient(135deg, var(--primary), #1d4ed8); padding:6px 10px; border-radius:999px; font-size:12px; letter-spacing:0.3px; }
        .price { font-size:20px; font-weight:700; color:var(--accent); }
        .stock { color: #111827; font-size: 14px; }
        
        form { margin-top:auto; display:flex; gap:10px; align-items:center; }
        input[type=number] {
          width:70px; padding:8px; border-radius:10px;
          border:1px solid rgba(0,0,0,0.25); background:#f8fafc; color:#111827;
        }
        
        .btn { flex:1; background:var(--primary); color:white; border:none; padding:10px 12px; border-radius:10px; font-weight:700; cursor:pointer; transition:transform .1s ease, box-shadow .2s ease; box-shadow:var(--shadow); }
        .btn:hover { transform:translateY(-1px); box-shadow:0 14px 44px rgba(37,99,235,0.35); }
        .alert { margin-top:14px; padding:12px 14px; border-radius:10px; background:rgba(34,197,94,0.12); color:#22c55e; border:1px solid rgba(34,197,94,0.3); }
    </style>
</head>
<body>
<div class="page"> 
    <div class="welcome">
        <div>
            <h1 style="margin:0; font-size: 26px; color: var(--primary); display: flex; align-items: center; gap: 8px;">
                🛍️ Müşteri Paneli
            </h1>
            <p style="margin: 4px 0 0 0; color: var(--muted); font-size: 14px;">
                Hoş geldin, <?php echo htmlspecialchars($ad_soyad); ?>
            </p>
        </div>
    </div>

    <div class="topbar">
        <?php include 'menu.php'; ?>
        <div style="display:flex; gap:10px; align-items:center; margin-left:auto;">
            <span class="pill">Ürün Kataloğu</span>
        </div>
    </div>

    <?php if(isset($mesaj)): ?>
        <div class="alert"><?php echo htmlspecialchars($mesaj); ?></div>
    <?php endif; ?>

    <div class="grid">
        <?php if (empty($urunler)): ?>
            <p>Şu an satışta ürün bulunmamaktadır.</p>
        <?php else: ?>
            <?php foreach($urunler as $row): ?>
                <div class="card">
                    <div style="display:flex; align-items:center; gap:10px; justify-content:space-between;">
                        <h3 style="margin:0; color:#111827;"><?php echo htmlspecialchars($row['UrunAdi']); ?></h3>
                        <span class="category"><?php echo htmlspecialchars($row['KategoriAdi']); ?></span>
                    </div>
                    <div class="price"><?php echo number_format($row['Fiyat'], 2); ?> ₺</div>
                    <div class="stock">Stok: <?php echo (int)$row['StokAdedi']; ?></div>
                    <form method="post">
                        <input type="hidden" name="urun_id" value="<?php echo (int)$row['UrunID']; ?>">
                        <input type="number" name="adet" value="1" min="1" max="<?php echo (int)$row['StokAdedi']; ?>">
                        <button type="submit" name="sepete_ekle" class="btn">Sepete Ekle</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>