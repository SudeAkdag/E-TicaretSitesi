<style>
    /* Menü İçin Modern Stil (Koyu Tema Uyumlu) */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(15, 23, 42, 0.95); /* Kart rengiyle uyumlu */
        padding: 14px 20px;
        border-radius: 12px;
        border: 1px solid rgba(148, 163, 184, 0.15);
        margin-bottom: 25px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
    }

    .nav-links {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .nav-item {
        text-decoration: none;
        color: #e5e7eb; /* Açık gri metin */
        font-weight: 500;
        font-size: 14px;
        padding: 8px 14px;
        border-radius: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid transparent;
    }

    /* Linklerin üzerine gelince */
    .nav-item:hover {
        background: rgba(37, 99, 235, 0.15); /* Primary mavi tonu */
        color: #60a5fa; /* Parlak mavi metin */
        border-color: rgba(37, 99, 235, 0.3);
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .logout-btn {
        background: rgba(239, 68, 68, 0.1); /* Kırmızımsı */
        color: #f87171;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid rgba(239, 68, 68, 0.25);
        transition: all 0.2s;
    }

    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
    }

    .cart-badge {
        background: #2563eb;
        color: white;
        font-size: 11px;
        padding: 2px 7px;
        border-radius: 10px;
        font-weight: bold;
        min-width: 20px;
        text-align: center;
    }
</style>

<div class="navbar">
    <div class="nav-links">
        <a href="urunler.php" class="nav-item">
            🛍️ Ürünler
        </a>

        <a href="dashboard.php" class="nav-item">
            📦 Siparişlerim
        </a>

        <a href="sepet.php" class="nav-item">
            🛒 Sepetim
            <?php
            // Sepette kaç ürün var sayalım
            $cart_count = isset($_SESSION['sepet']) ? count($_SESSION['sepet']) : 0;
            if($cart_count > 0):
            ?>
                <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>

    </div>

    <div class="nav-right">
        <a href="../logout.php" class="logout-btn">Çıkış</a>

    </div>
</div>