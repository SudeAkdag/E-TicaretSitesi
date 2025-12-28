-- Proje Adi: E Ticaret Sitesi
--
-- Proje Uyeleri Ad Soyad Numara:
-- Elif Nehir Vursun 230601030
-- Sude Naz Akdag 230601017
-- Ayse Fulya Konuralp 230601007
-- Basak Eroglu 230609008
-- Eda Kaymakci 230609042

-- VERITABANI OLUSTURMA VE SIFIRLAMA
-- --------------------------------------------------------
DROP DATABASE IF EXISTS `eticaret_proje`;
CREATE DATABASE IF NOT EXISTS `eticaret_proje` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
SET SQL_SAFE_UPDATES = 0;
USE `eticaret_proje`;

-- --------------------------------------------------------
-- 1. TABLO: KATEGORI
-- --------------------------------------------------------
CREATE TABLE `kategori` (
  `KategoriID` int(11) NOT NULL AUTO_INCREMENT,
  `KategoriAdi` varchar(100) NOT NULL,
  PRIMARY KEY (`KategoriID`),
  UNIQUE KEY `KategoriAdi` (`KategoriAdi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kategori` (`KategoriID`, `KategoriAdi`) VALUES
(1, 'Elektronik'), (2, 'Giyim'), (3, 'Kitap'), (4, 'Ev Aletleri'), 
(5, 'Kozmetik'), (6, 'Spor Malzemeleri'), (7, 'Otomotiv'), 
(8, 'Oyuncak'), (9, 'Bahçe'), (10, 'Yiyecek');

-- --------------------------------------------------------
-- 2. TABLO: ROL
-- --------------------------------------------------------
CREATE TABLE `rol` (
  `RolID` int(11) NOT NULL AUTO_INCREMENT,
  `RolAdi` varchar(50) NOT NULL,
  PRIMARY KEY (`RolID`),
  UNIQUE KEY `RolAdi` (`RolAdi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rol` (`RolID`, `RolAdi`) VALUES
(1, 'Yonetici'), (2, 'Personel'), (3, 'Musteri');

-- --------------------------------------------------------
-- 3. TABLO: TEDARIKCI
-- --------------------------------------------------------
CREATE TABLE `tedarikci` (
  `TedarikciID` int(11) NOT NULL AUTO_INCREMENT,
  `TedarikciAdi` varchar(50) NOT NULL,
  `TedarikciSoyadi` varchar(50) DEFAULT NULL,
  `Telefon` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`TedarikciID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tedarikci` (`TedarikciID`, `TedarikciAdi`, `TedarikciSoyadi`, `Telefon`, `Email`) VALUES
(1, 'Global', 'Elektronik A.Ş.', '02124440001', 'info@global.com'),
(2, 'Hızlı', 'Tekstil Ltd.', '02124440002', 'satis@hizlitekstil.com'),
(3, 'Bilge', 'Yayıncılık', '02124440003', 'iletisim@bilgekitap.com'),
(4, 'Yıldırım', 'Ev Gereçleri', '02124440004', 'destek@yildirim.com'),
(5, 'Doğa', 'Kozmetik', '02124440005', 'info@dogakozmetik.com'),
(6, 'Sportif', 'Dünyası', '02124440006', 'satis@sportif.com'),
(7, 'Oto', 'Yedek Parça', '02124440007', 'servis@otoparca.com'),
(8, 'Mutlu', 'Oyuncak', '02124440008', 'info@mutluoyuncak.com'),
(9, 'Yeşil', 'Bahçe Market', '02124440009', 'satis@yesilbahce.com'),
(10, 'Lezzet', 'Gıda Toptan', '02124440010', 'siparis@lezzetgida.com');

-- --------------------------------------------------------
-- 4. TABLO: KULLANICI
-- --------------------------------------------------------
CREATE TABLE `kullanici` (
  `KullaniciID` int(11) NOT NULL AUTO_INCREMENT,
  `Email` varchar(100) NOT NULL,
  `Sifre` char(60) NOT NULL,
  `RolID` int(11) DEFAULT NULL,
  `Durum` enum('Aktif','Pasif') DEFAULT 'Aktif',
  PRIMARY KEY (`KullaniciID`),
  UNIQUE KEY `Email` (`Email`),
  FOREIGN KEY (`RolID`) REFERENCES `rol` (`RolID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kullanici` (`KullaniciID`, `Email`, `Sifre`, `RolID`, `Durum`) VALUES
(1, 'ahmet.yilmaz@mail.com', '123', 3, 'Aktif'), 
(2, 'mehmet.demir@mail.com', '123', 3, 'Aktif'),
(3, 'ayse.kara@mail.com', '123', 3, 'Aktif'), 
(4, 'fatma.sahin@mail.com', '123', 3, 'Aktif'),
(5, 'ali.koc@mail.com', '123', 3, 'Aktif'), 
(6, 'zeynep.cetin@mail.com', '123', 3, 'Aktif'),
(7, 'emre.celik@mail.com', '123', 3, 'Aktif'), 
(8, 'melike.aslan@mail.com', '123', 3, 'Aktif'),
(9, 'baris.kaya@mail.com', '123', 3, 'Aktif'), 
(10, 'seda.bulut@mail.com', '123', 3, 'Aktif'),
(11, 'mert.aksoy@sirket.com', '123', 2, 'Aktif'), 
(12, 'hale.kurt@sirket.com', '123', 2, 'Aktif'),
(13, 'selim.arslan@sirket.com', '123', 2, 'Aktif'), 
(14, 'pelin.gok@sirket.com', '123', 2, 'Aktif'),
(15, 'okan.ates@sirket.com', '123', 2, 'Aktif'), 
(16, 'serkan.boz@sirket.com', '123', 2, 'Aktif'),
(17, 'deniz.eren@sirket.com', '123', 2, 'Aktif'), 
(18, 'ahu.ozturk@sirket.com', '123', 2, 'Aktif'),
(19, 'berk.yalcin@sirket.com', '123', 2, 'Aktif'), 
(20, 'elif.dogan@sirket.com', '123', 2, 'Aktif'),
(21, 'yonetici1@sirket.com', '123', 1, 'Aktif'), 
(22, 'yonetici2@sirket.com', '123', 1, 'Aktif');

-- --------------------------------------------------------
-- 5. TABLO: URUN
-- --------------------------------------------------------
CREATE TABLE `urun` (
  `UrunID` int(11) NOT NULL AUTO_INCREMENT,
  `UrunAdi` varchar(200) NOT NULL,
  `Fiyat` decimal(10,2) NOT NULL,
  `StokAdedi` int(11) NOT NULL DEFAULT 0,
  `KategoriID` int(11) DEFAULT NULL,
  PRIMARY KEY (`UrunID`),
  FOREIGN KEY (`KategoriID`) REFERENCES `kategori` (`KategoriID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `urun` (`UrunID`, `UrunAdi`, `Fiyat`, `StokAdedi`, `KategoriID`) VALUES
(1, 'Laptop Pro 15', 25000.00, 20, 1), 
(2, 'Kablosuz Kulaklık', 500.00, 100, 1),
(3, 'Pamuklu T-Shirt', 150.00, 200, 2), 
(4, 'Yazılım Mühendisliği Kitabı', 120.00, 50, 3),
(5, 'Filtre Kahve Makinesi', 1800.00, 30, 4), 
(6, 'Nemlendirici Yüz Kremi', 450.00, 150, 5),
(7, 'Koşu Bandı Ev Tipi', 5500.00, 10, 6), 
(8, 'Kış Lastiği 205/55', 1200.00, 40, 7),
(9, 'Eğitici Lego Seti', 350.00, 80, 8), 
(10, 'Organik Bitki Çayı', 75.00, 300, 10),
(11, 'Akıllı Saat V2', 3200.00, 45, 1), 
(12, 'Çelik Termos 1L', 280.00, 120, 6);

-- --------------------------------------------------------
-- 6. TABLO: URUNTEDARIKCI
-- --------------------------------------------------------
CREATE TABLE `uruntedarikci` (
  `UrunID` int(11) NOT NULL,
  `TedarikciID` int(11) NOT NULL,
  PRIMARY KEY (`UrunID`,`TedarikciID`),
  FOREIGN KEY (`UrunID`) REFERENCES `urun` (`UrunID`),
  FOREIGN KEY (`TedarikciID`) REFERENCES `tedarikci` (`TedarikciID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `uruntedarikci` (`UrunID`, `TedarikciID`) VALUES
(1, 1), (2, 1), (3, 2), (4, 3), (5, 4), (6, 5), 
(7, 6), (8, 7), (9, 8), (10, 10), (11, 1), (12, 6);

-- --------------------------------------------------------
-- 7. TABLO: PERSONEL
-- --------------------------------------------------------
CREATE TABLE `personel` (
  `PersonelID` int(11) NOT NULL AUTO_INCREMENT,
  `KullaniciID` int(11) DEFAULT NULL,
  `Ad` varchar(50) NOT NULL,
  `Soyad` varchar(50) NOT NULL,
  `Telefon` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`PersonelID`),
  UNIQUE KEY `KullaniciID` (`KullaniciID`),
  FOREIGN KEY (`KullaniciID`) REFERENCES `kullanici` (`KullaniciID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `personel` (`PersonelID`, `KullaniciID`, `Ad`, `Soyad`, `Telefon`) VALUES
(1, 11, 'Mert', 'Aksoy', '05331110000'), 
(2, 12, 'Hale', 'Kurt', '05332220000'),
(3, 13, 'Selim', 'Arslan', '05333330000'), 
(4, 14, 'Pelin', 'Gök', '05334440000'),
(5, 15, 'Okan', 'Ateş', '05335550000'), 
(6, 16, 'Serkan', 'Boz', '05336660000'),
(7, 17, 'Deniz', 'Eren', '05337770000'), 
(8, 18, 'Ahu', 'Öztürk', '05338880000'),
(9, 19, 'Berk', 'Yalçın', '05339990000'), 
(10, 20, 'Elif', 'Doğan', '05330001111');

-- --------------------------------------------------------
-- 8. TABLO: MUSTERI
-- --------------------------------------------------------
CREATE TABLE `musteri` (
  `MusteriID` int(11) NOT NULL AUTO_INCREMENT,
  `KullaniciID` int(11) DEFAULT NULL,
  `Ad` varchar(50) NOT NULL,
  `Soyad` varchar(50) NOT NULL,
  `Telefon` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`MusteriID`),
  UNIQUE KEY `KullaniciID` (`KullaniciID`),
  FOREIGN KEY (`KullaniciID`) REFERENCES `kullanici` (`KullaniciID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `musteri` (`MusteriID`, `KullaniciID`, `Ad`, `Soyad`, `Telefon`) VALUES
(1, 1, 'Ahmet', 'Yılmaz', '05001112233'), 
(2, 2, 'Mehmet', 'Demir', '05002223344'),
(3, 3, 'Ayşe', 'Kara', '05003334455'), 
(4, 4, 'Fatma', 'Şahin', '05004445566'),
(5, 5, 'Ali', 'Koç', '05005556677'), 
(6, 6, 'Zeynep', 'Çetin', '05006667788'),
(7, 7, 'Emre', 'Çelik', '05007778899'), 
(8, 8, 'Melike', 'Aslan', '05008889900'),
(9, 9, 'Barış', 'Kaya', '05009990011'), 
(10, 10, 'Seda', 'Bulut', '05001002030');

-- --------------------------------------------------------
-- 9. TABLO: ADRES
-- --------------------------------------------------------
CREATE TABLE `adres` (
  `AdresID` int(11) NOT NULL AUTO_INCREMENT,
  `MusteriID` int(11) DEFAULT NULL,
  `AcikAdres` text NOT NULL,
  PRIMARY KEY (`AdresID`),
  FOREIGN KEY (`MusteriID`) REFERENCES `musteri` (`MusteriID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `adres` (`AdresID`, `MusteriID`, `AcikAdres`) VALUES
(1, 1, 'Ankara Çankaya 12/4'), 
(2, 2, 'İstanbul Kadıköy 22/5'),
(3, 3, 'İzmir Karşıyaka 45/9'), 
(4, 4, 'Bursa Nilüfer 33/2'),
(5, 5, 'Antalya Lara 19/7'), 
(6, 6, 'Eskişehir Odunpazarı 5/2'),
(7, 7, 'Trabzon Ortahisar 14/99'), 
(8, 8, 'Konya Meram Çiçek Sk 1'),
(9, 9, 'Gaziantep Şahinbey 8/10'), 
(10, 10, 'Mersin Mezitli 21/88');

-- --------------------------------------------------------
-- 10. TABLO: SIPARIS
-- --------------------------------------------------------
CREATE TABLE `siparis` (
  `SiparisID` int(11) NOT NULL AUTO_INCREMENT,
  `MusteriID` int(11) DEFAULT NULL,
  `SiparisTarihi` datetime DEFAULT current_timestamp(),
  `ToplamTutar` decimal(10,2) NOT NULL DEFAULT 0.00,
  `Durum` enum('Beklemede','Hazirlaniyor','Kargoda','Teslim Edildi','Iptal') DEFAULT 'Beklemede',
  `AdresID` int(11) DEFAULT NULL,
  PRIMARY KEY (`SiparisID`),
  FOREIGN KEY (`MusteriID`) REFERENCES `musteri` (`MusteriID`),
  FOREIGN KEY (`AdresID`) REFERENCES `adres` (`AdresID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Burada henüz ürün olmadığı için ToplamTutar 0.00'dır.
-- En alttaki UPDATE kodu bunları otomatik dolduracaktır.
INSERT INTO `siparis` (`SiparisID`, `MusteriID`, `SiparisTarihi`, `ToplamTutar`, `Durum`, `AdresID`) VALUES
(1, 1, '2025-10-15 10:00:00', 0.00, 'Teslim Edildi', 1),
(2, 2, '2025-10-16 11:30:00', 0.00, 'Hazirlaniyor', 2),
(3, 3, '2025-10-17 14:45:00', 0.00, 'Hazirlaniyor', 3),
(4, 4, '2025-10-18 09:15:00', 0.00, 'Kargoda', 4),
(5, 5, '2025-10-19 16:20:00', 0.00, 'Teslim Edildi', 5),
(6, 6, '2025-10-20 12:00:00', 0.00, 'Iptal', 6),
(7, 7, '2025-10-21 15:50:00', 0.00, 'Hazirlaniyor', 7),
(8, 8, '2025-10-22 08:30:00', 0.00, 'Beklemede', 8),
(9, 9, '2025-10-23 18:00:00', 0.00, 'Kargoda', 9),
(10, 10, '2025-10-24 13:10:00', 0.00, 'Teslim Edildi', 10),
(11, 1, '2025-10-25 09:00:00', 0.00, 'Beklemede', NULL),
(12, 2, '2025-10-26 10:00:00', 0.00, 'Hazirlaniyor', NULL);

-- --------------------------------------------------------
-- 11. TABLO: SIPARISDETAY
-- --------------------------------------------------------
CREATE TABLE `siparisdetay` (
  `SiparisID` int(11) NOT NULL,
  `UrunID` int(11) NOT NULL,
  `Adet` int(11) NOT NULL,
  `BirimFiyat` decimal(10,2) NOT NULL,
  PRIMARY KEY (`SiparisID`,`UrunID`),
  FOREIGN KEY (`SiparisID`) REFERENCES `siparis` (`SiparisID`),
  FOREIGN KEY (`UrunID`) REFERENCES `urun` (`UrunID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `siparisdetay` (`SiparisID`, `UrunID`, `Adet`, `BirimFiyat`) VALUES
(1, 1, 1, 25000.00), (2, 2, 2, 500.00), (3, 5, 1, 1800.00), 
(4, 3, 3, 150.00), (5, 6, 1, 450.00), (6, 7, 1, 5500.00), 
(7, 8, 4, 1200.00), (8, 9, 2, 350.00), (9, 11, 1, 3200.00), 
(10, 10, 5, 75.00), (11, 4, 1, 120.00), (12, 12, 2, 280.00);

-- --------------------------------------------------------
-- 12. TABLO: STOKHAREKETI
-- --------------------------------------------------------
CREATE TABLE `stokhareketi` (
  `HareketID` int(11) NOT NULL AUTO_INCREMENT,
  `UrunID` int(11) DEFAULT NULL,
  `PersonelID` int(11) DEFAULT NULL,
  `Tarih` datetime DEFAULT current_timestamp(),
  `Miktar` int(11) NOT NULL,
  `HareketTuru` enum('Giris','Cikis','Iade') NOT NULL,
  PRIMARY KEY (`HareketID`),
  FOREIGN KEY (`UrunID`) REFERENCES `urun` (`UrunID`),
  FOREIGN KEY (`PersonelID`) REFERENCES `personel` (`PersonelID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stokhareketi` (`HareketID`, `UrunID`, `PersonelID`, `Tarih`, `Miktar`, `HareketTuru`) VALUES
(1, 1, 1, '2025-10-01 09:00:00', 25, 'Giris'), 
(2, 2, 1, '2025-10-01 09:10:00', 100, 'Giris'),
(3, 3, 2, '2025-10-02 10:00:00', 200, 'Giris'), 
(4, 4, 2, '2025-10-02 11:00:00', 50, 'Giris'),
(5, 5, 4, '2025-10-03 14:00:00', 30, 'Giris'), 
(6, 6, 3, '2025-10-03 15:00:00', 150, 'Giris'),
(7, 7, 3, '2025-10-04 09:30:00', 15, 'Giris'), 
(8, 8, 5, '2025-10-04 10:45:00', 50, 'Giris'),
(9, 9, 6, '2025-10-05 11:20:00', 100, 'Giris'), 
(10, 10, 7, '2025-10-05 16:00:00', 300, 'Giris'),
(11, 11, 8, '2025-10-06 09:00:00', 50, 'Giris'),
(12, 12, 9, '2025-10-06 10:00:00', 150, 'Giris'),
(13, 1, 10, '2025-10-07 14:00:00', 5, 'Cikis'),
(14, 2, 1, '2025-10-07 15:00:00', 2, 'Cikis'),
(15, 3, 2, '2025-10-08 16:00:00', 10, 'Giris');

-- --------------------------------------------------------
-- STORED PROCEDURES (SAKLI YORDAMLAR)
-- --------------------------------------------------------

DELIMITER //

CREATE PROCEDURE `SP_BeklemedeOlanSiparisler` ()
BEGIN
  SELECT S.SiparisID, S.SiparisTarihi, S.ToplamTutar, S.Durum, M.Ad AS MusteriAd, M.Soyad AS MusteriSoyad, K.Email AS MusteriEmail, A.AcikAdres AS TeslimatAdresi 
  FROM SIPARIS S 
  INNER JOIN MUSTERI M ON S.MusteriID = M.MusteriID 
  INNER JOIN KULLANICI K ON M.KullaniciID = K.KullaniciID 
  INNER JOIN ADRES A ON S.AdresID = A.AdresID 
  ORDER BY FIELD(S.Durum, 'Beklemede', 'Hazirlaniyor', 'Kargoda', 'Teslim Edildi', 'Iptal'), S.SiparisTarihi ASC;
END //

CREATE PROCEDURE `SP_EnCokSatanUrunler` ()
BEGIN
  SELECT U.UrunID, U.UrunAdi, SUM(SD.Adet) AS ToplamSatilanAdet, COUNT(DISTINCT S.MusteriID) AS FarkliMusteriSayisi 
  FROM URUN U 
  INNER JOIN SIPARISDETAY SD ON U.UrunID = SD.UrunID 
  INNER JOIN SIPARIS S ON SD.SiparisID = S.SiparisID 
  GROUP BY U.UrunID, U.UrunAdi 
  ORDER BY ToplamSatilanAdet DESC LIMIT 10;
END //

CREATE PROCEDURE `SP_MusteriSiparisDetaylari` (IN `p_MusteriID` INT)
BEGIN
  SELECT S.SiparisID, S.SiparisTarihi, S.ToplamTutar, S.Durum AS SiparisDurumu, A.AcikAdres, U.UrunAdi, SD.Adet, SD.BirimFiyat 
  FROM MUSTERI M 
  INNER JOIN SIPARIS S ON M.MusteriID = S.MusteriID 
  INNER JOIN ADRES A ON S.AdresID = A.AdresID 
  INNER JOIN SIPARISDETAY SD ON S.SiparisID = SD.SiparisID 
  INNER JOIN URUN U ON SD.UrunID = U.UrunID 
  WHERE M.MusteriID = p_MusteriID 
  ORDER BY S.SiparisTarihi DESC;
END //

CREATE PROCEDURE `SP_PersonelStokHareketleri` (IN `p_PersonelID` INT)
BEGIN
  SELECT P.Ad AS PersonelAd, P.Soyad AS PersonelSoyad, U.UrunAdi, SH.Tarih, SH.Miktar, SH.HareketTuru 
  FROM PERSONEL P 
  INNER JOIN STOKHAREKETI SH ON P.PersonelID = SH.PersonelID 
  INNER JOIN URUN U ON SH.UrunID = U.UrunID 
  WHERE P.PersonelID = p_PersonelID 
  ORDER BY SH.Tarih DESC;
END //

CREATE PROCEDURE `SP_TedarikciSattigiUrunSayisi` ()
BEGIN
  SELECT T.TedarikciAdi, T.TedarikciSoyadi, COUNT(UT.UrunID) AS TedarikEdilenUrunCesidi, GROUP_CONCAT(DISTINCT K.KategoriAdi SEPARATOR ', ') AS TedarikEdilenKategoriler 
  FROM TEDARIKCI T 
  INNER JOIN URUNTEDARIKCI UT ON T.TedarikciID = UT.TedarikciID 
  INNER JOIN URUN U ON UT.UrunID = U.UrunID 
  INNER JOIN KATEGORI K ON U.KategoriID = K.KategoriID 
  GROUP BY T.TedarikciID, T.TedarikciAdi, T.TedarikciSoyadi 
  ORDER BY TedarikEdilenUrunCesidi DESC;
END //

CREATE PROCEDURE SP_SiparisFaturaDetayi (IN p_SiparisID INT)
BEGIN
  SELECT 
    S.SiparisID, 
    S.SiparisTarihi, 
    CONCAT(M.Ad, ' ', M.Soyad) AS MusteriTamAd, 
    A.AcikAdres, 
    U.UrunAdi, 
    SD.Adet, 
    U.Fiyat AS BirimFiyat, 
    (SD.Adet * U.Fiyat) AS SatirToplami
  FROM SIPARIS S
  INNER JOIN MUSTERI M ON S.MusteriID = M.MusteriID
  INNER JOIN ADRES A ON S.AdresID = A.AdresID
  INNER JOIN SIPARISDETAY SD ON S.SiparisID = SD.SiparisID
  INNER JOIN URUN U ON SD.UrunID = U.UrunID 
  WHERE S.SiparisID = p_SiparisID;
END //

CREATE PROCEDURE `SP_SehirBazliSatisAnalizi` ()
BEGIN
  SELECT 
    A.AcikAdres AS Lokasyon, 
    COUNT(DISTINCT S.SiparisID) AS SiparisSayisi, 
    SUM(SD.Adet * FN_KdvHesapla(SD.BirimFiyat, 20)) AS ToplamCiro
  FROM ADRES A
  INNER JOIN SIPARIS S ON A.AdresID = S.AdresID
  INNER JOIN SIPARISDETAY SD ON S.SiparisID = SD.SiparisID
  GROUP BY A.AcikAdres
  ORDER BY ToplamCiro DESC;
END //

CREATE PROCEDURE `SP_KategoriUrunListesi` (IN `p_KategoriID` INT)
BEGIN
  SELECT UrunID, UrunAdi, Fiyat, StokAdedi FROM URUN WHERE KategoriID = p_KategoriID;
END //

CREATE PROCEDURE `SP_KullaniciGirisKontrol` (IN `p_Email` VARCHAR(100))
BEGIN
  SELECT 
    K.KullaniciID, 
    K.Sifre, 
    K.RolID, 
    K.Durum,
    COALESCE(M.Ad, P.Ad, 'Yönetici') AS Ad,
    COALESCE(M.Soyad, P.Soyad, '') AS Soyad
  FROM KULLANICI K
  LEFT JOIN MUSTERI M ON K.KullaniciID = M.KullaniciID
  LEFT JOIN PERSONEL P ON K.KullaniciID = P.KullaniciID
  WHERE K.Email = p_Email AND K.Durum = 'Aktif';
END //

CREATE PROCEDURE `SP_MusteriAdresleri` (IN `p_MusteriID` INT)
BEGIN
  SELECT AdresID, AcikAdres FROM ADRES WHERE MusteriID = p_MusteriID;
END //

CREATE PROCEDURE `SP_RolAdiniGetir` (IN `p_RolID` INT)
BEGIN
  SELECT RolAdi FROM ROL WHERE RolID = p_RolID;
END //

CREATE PROCEDURE `SP_SiparisDurumGuncelle` (IN `p_SiparisID` INT, IN `p_YeniDurum` ENUM('Beklemede','Hazirlaniyor','Kargoda','Teslim Edildi','Iptal'))
BEGIN
  UPDATE SIPARIS SET Durum = p_YeniDurum WHERE SiparisID = p_SiparisID;
  SELECT 'Sipariş durumu güncellendi.' AS Sonuc;
END //

CREATE PROCEDURE `SP_StokDusukUrunler` (IN `p_Limit` INT)
BEGIN
  SELECT UrunID, UrunAdi, StokAdedi FROM URUN WHERE StokAdedi < p_Limit ORDER BY StokAdedi ASC;
END //

CREATE PROCEDURE `SP_ToplamSiparisTutariniGetir` ()
BEGIN
  SELECT SUM(ToplamTutar) AS ToplamTutar FROM SIPARIS WHERE Durum = 'Teslim Edildi';
END //

CREATE PROCEDURE `SP_UrunFiyatGuncelle` (IN `p_UrunID` INT, IN `p_YeniFiyat` DECIMAL(10,2))
BEGIN
  UPDATE URUN SET Fiyat = p_YeniFiyat WHERE UrunID = p_UrunID;
  SELECT 'Ürün fiyatı güncellendi.' AS Sonuc;
END //

CREATE PROCEDURE `SP_YeniAdresEkle` (IN `p_MusteriID` INT, IN `p_AcikAdres` TEXT)
BEGIN
  INSERT INTO ADRES (MusteriID, AcikAdres) VALUES (p_MusteriID, p_AcikAdres);
  SELECT LAST_INSERT_ID() AS YeniAdresID;
END //

CREATE PROCEDURE `SP_MusteriHarcamaRaporu_Cursor` ()
BEGIN
  
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_musteri_ad VARCHAR(100);
    DECLARE v_toplam_harcama DECIMAL(10,2);
    
    -- Cursor Tanımlama (Müşteri adlarını ve toplam sipariş tutarlarını çeker) 
    DECLARE cur_musteri CURSOR FOR 
        SELECT CONCAT(Ad, ' ', Soyad), SUM(S.ToplamTutar) 
        FROM musteri M
        INNER JOIN siparis S ON M.MusteriID = S.MusteriID
        GROUP BY M.MusteriID;
        
    -- Bitiş Kontrolü (Hata yakalayıcı) 
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    CREATE TEMPORARY TABLE IF NOT EXISTS MusteriOzet (
        MusteriBilgi VARCHAR(100),
        HarcamaTutari DECIMAL(10,2),
        Durum VARCHAR(20)
    );
    TRUNCATE TABLE MusteriOzet;

    OPEN cur_musteri; 

    read_loop: LOOP
        FETCH cur_musteri INTO v_musteri_ad, v_toplam_harcama; 
        IF done THEN
            LEAVE read_loop;
        END IF;

        -- İş Mantığı: Harcamaya göre statü belirle
        IF v_toplam_harcama > 5000 THEN
            INSERT INTO MusteriOzet VALUES (v_musteri_ad, v_toplam_harcama, 'VIP Müşteri');
        ELSE
            INSERT INTO MusteriOzet VALUES (v_musteri_ad, v_toplam_harcama, 'Standart');
        END IF;
    END LOOP;

    CLOSE cur_musteri; 

    SELECT * FROM MusteriOzet;
END //

CREATE FUNCTION `FN_KdvHesapla` (p_fiyat DECIMAL(10,2), p_kdv_orani INT) 
RETURNS DECIMAL(10,2)
DETERMINISTIC -- Aynı girdi için her zaman aynı sonucu verir 
BEGIN
    DECLARE v_son_fiyat DECIMAL(10,2);
    SET v_son_fiyat = p_fiyat + (p_fiyat * p_kdv_orani / 100);
    RETURN v_son_fiyat; -- Sonucu döndür 
END //


DELIMITER ;

-- --------------------------------------------------------
-- TRIGGERLAR (TETİKLEYİCİLER)
-- --------------------------------------------------------

DELIMITER //

-- 1. Trigger: Sipariş İptal Edilirse Stoğu İade Et
CREATE TRIGGER `TR_IptalDurumundaStokIade` AFTER UPDATE ON `siparis` FOR EACH ROW BEGIN 
  IF NEW.Durum = 'Iptal' AND OLD.Durum <> 'Iptal' THEN 
    INSERT INTO STOKHAREKETI (UrunID, PersonelID, Miktar, HareketTuru) 
    SELECT SD.UrunID, 1, SD.Adet, 'Iade' FROM SIPARISDETAY SD WHERE SD.SiparisID = NEW.SiparisID; 
    UPDATE URUN U INNER JOIN SIPARISDETAY SD ON U.UrunID = SD.UrunID SET U.StokAdedi = U.StokAdedi + SD.Adet WHERE SD.SiparisID = NEW.SiparisID; 
  END IF; 
END //

-- 2. Trigger: Sipariş Detay Eklendiğinde Sipariş Toplam Tutarını Güncelle
CREATE TRIGGER `TR_SiparisToplamTutarGuncelle` AFTER INSERT ON `siparisdetay` FOR EACH ROW 
BEGIN 
  -- Fonksiyon yardımıyla KDV dahil toplam tutar güncellenir
  UPDATE SIPARIS SET ToplamTutar = (
    SELECT SUM(Adet * FN_KdvHesapla(BirimFiyat, 20)) 
    FROM SIPARISDETAY 
    WHERE SiparisID = NEW.SiparisID
  ) WHERE SiparisID = NEW.SiparisID; 
END //

-- 3. Trigger: Sipariş Eklendiğinde Stoğu Düş ve Hareket Kaydet (PERSONEL ID: NULL OLARAK AYARLANDI)
CREATE TRIGGER `TR_StokGuncelle_SiparisEkleme` AFTER INSERT ON `siparisdetay` FOR EACH ROW BEGIN 
  -- 1. Ürünün stok adedini düş
  UPDATE URUN SET StokAdedi = StokAdedi - NEW.Adet WHERE UrunID = NEW.UrunID;

  -- 2. Stok hareketini kaydet (PersonelID yerine NULL gönderiyoruz)
  -- NULL olması, bu düşüşün "Online Satış" kaynaklı olduğunu gösterir.
  INSERT INTO STOKHAREKETI (UrunID, PersonelID, Miktar, HareketTuru) 
  VALUES (NEW.UrunID, NULL, -NEW.Adet, 'Cikis');
END //

DELIMITER ;

-- --------------------------------------------------------
-- OTOMATİK HESAPLAMA BAŞLATILIYOR...
-- (Bu kod 0.00 olarak girilen tüm siparişleri, ürünlerin fiyatına göre KDV fonksiyonunu kullanarak sipariş tutarlarını otomatik günceller (%20 KDV ile))
-- --------------------------------------------------------
UPDATE siparis s
INNER JOIN (
    SELECT SiparisID, SUM(Adet * FN_KdvHesapla(BirimFiyat, 20)) as KdvliGercekToplam
    FROM siparisdetay
    GROUP BY SiparisID
) d ON s.SiparisID = d.SiparisID
SET s.ToplamTutar = d.KdvliGercekToplam;