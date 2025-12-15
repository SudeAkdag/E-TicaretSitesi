<?php
// /personel/tedarik_raporu.php
session_start();
include '../db_config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['rol_id'] != 2) {
    header("location: ../login.php"); exit;
}

// Saklı Yordamı (Stored Procedure) Çağır
// Bu SP; Tedarikçileri, sağladıkları ürün sayısını ve kategorileri JOIN yaparak getirir.
$sql = "CALL SP_TedarikciSattigiUrunSayisi()";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Tedarikçi Raporu</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-container">
        <?php include 'menu.php'; ?>

        <div class="header">
            <div>
                <h1>📊 Tedarikçi Analiz Raporu</h1>
            </div>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th style="text-align:left;">Tedarikçi Adı</th>
                        <th>Yetkili</th>
                        <th>Ürün Çeşit Sayısı</th>
                        <th style="text-align:left;">Kategoriler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td style="font-weight:bold; color: black;">
                             <?php echo $row['TedarikciAdi']; ?>
                        </td>
                        
                        <td><?php echo $row['TedarikciSoyadi']; ?></td>
                        
                        <td style="text-align:center;">
                            <span class="status-active" style="font-size:14px; padding: 4px 12px;">
                                <?php echo $row['TedarikEdilenUrunCesidi']; ?> Adet
                            </span>
                        </td>

                        <td style="color: black; font-size:13px;">
                            <?php echo $row['TedarikEdilenKategoriler']; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php while($conn->more_results() && $conn->next_result()); ?>
    </div>
</body>
</html>