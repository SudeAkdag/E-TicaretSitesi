<?php
// /personel/menu.php
// Mevcut sayfanın adını alarak aktif menüyü boyayacağız
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .navbar {
        background: rgba(15, 23, 42, 0.95);
        padding: 15px 25px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    }
    .nav-links { display: flex; gap: 10px; }
    .nav-item {
        color: #94a3b8;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .nav-item:hover { background: rgba(255, 255, 255, 0.05); color: #fff; }
    .nav-item.active {
        background: rgba(37, 99, 235, 0.15);
        color: #60a5fa;
        border: 1px solid rgba(37, 99, 235, 0.3);
    }
    .logout-btn {
        background: rgba(239, 68, 68, 0.1);
        color: #f87171;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .logout-btn:hover { background: rgba(239, 68, 68, 0.2); }
</style>

<div class="navbar">
    <div class="nav-links">
        <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            📦 Siparişler
        </a>
        <a href="urunler.php" class="nav-item <?php echo $current_page == 'urunler.php' ? 'active' : ''; ?>">
            🏷️ Ürün & Stok Yönetimi
        </a>
        <a href="tedarik_raporu.php" class="nav-item <?php echo $current_page == 'tedarik_raporu.php' ? 'active' : ''; ?>">
            📊 Tedarikçi Raporu
        </a>
        <div class="nav-links">
    <a href="hareketlerim.php" class="nav-item">📝 Hareketlerim</a> 
</div>
    </div>
    <div style="display:flex; align-items:center; gap:15px;">
        <span style="color:#94a3b8; font-size:14px;">
            👤 <?php echo isset($_SESSION['ad_soyad']) ? $_SESSION['ad_soyad'] : 'Personel'; ?>
        </span>
        <a href="../logout.php" class="logout-btn">Çıkış Yap</a>
    </div>
</div>