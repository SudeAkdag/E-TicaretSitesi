<style>
    .navbar {
        background-color: #1e293b;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .nav-links {
        display: flex;
        gap: 15px;
    }
    .nav-item {
        color: #e2e8f0;
        text-decoration: none;
        font-weight: 500;
        padding: 8px 16px;
        border-radius: 6px;
        transition: all 0.2s;
        font-size: 14px;
    }
    .nav-item:hover {
        background-color: #334155;
        color: white;
    }
    .nav-item.active {
        background-color: #3b82f6;
        color: white;
    }
    .logout {
        background-color: #ef4444;
        color: white;
    }
    .logout:hover {
        background-color: #dc2626;
    }
</style>

<?php
// Hangi sayfada olduğumuzu bulalım (Aktif linki boyamak için)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="navbar">
    <div style="font-weight: bold; color: white; font-size: 18px;">👑 Yönetici Paneli</div>
    
    <div class="nav-links">
        <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
            📊 Genel Durum
        </a>
        
        <a href="musteri_harcama_raporu.php" class="nav-item <?php echo $current_page == 'musteri_harcama_raporu.php' ? 'active' : ''; ?>">
            📈 Müşteri Sadakat Raporu
        </a>
        
        <a href="../logout.php" class="nav-item logout">
            🚪 Çıkış Yap
        </a>
    </div>
</div>