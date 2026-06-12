<?php
session_start();

// 1. Cek jika TIDAK login
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header('Location: login.html?redirect_url=' . $redirect_url);
    exit;
}

// 2. Cek jika BUKAN ADMIN
if ($_SESSION['user_role'] !== 'admin') {
    header('Location: index.html'); 
    exit;
}

include 'db.php';

// Hitung statistik untuk KPI Cards
$stmt = $pdo->query("SELECT COUNT(*) as total FROM queues");
$total_pasien = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM queues WHERE DATE(timestamp) = CURDATE()");
$stmt->execute();
$antrian_hari_ini = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(DISTINCT poli) as total FROM queues WHERE poli IS NOT NULL AND poli != ''");
$poli_aktif = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Antrian - AntriCare</title>
    <link rel="stylesheet" href="admin_style.css"> 
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">
            <img src="images/logo.png" alt="Logo AntriCare" class="logo-img">
            <span>AntriCare - Admin Panel</span>
        </div>
        <ul class="nav-links">
            <li><a href="admin.php">MANAJEMEN ANTRIAN</a></li>
            <li><a href="kelola_jadwal.php">KELOLA JADWAL</a></li> 
            <li><a href="laporan.php">LAPORAN</a></li>
            <li><a href="prediksi.php">PREDIKSI</a></li>
        </ul>
        <div class="nav-buttons" id="nav-auth">
            <a href="logout.php" class="btn btn-login">Logout</a>
        </div>
    </nav>
</header>

<main class="page-container">
   <!-- KPI CARDS YANG DIPERBAIKI -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-info">
            <h4>📊 TOTAL PASIEN</h4>
            <div class="kpi-number"><?php echo number_format($total_pasien); ?></div>
        </div>
        <div class="kpi-icon">👥</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-info">
            <h4>📋 ANTRIAN HARI INI</h4>
            <div class="kpi-number"><?php echo number_format($antrian_hari_ini); ?></div>
        </div>
        <div class="kpi-icon">🎫</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-info">
            <h4>🏥 POLI AKTIF</h4>
            <div class="kpi-number"><?php echo $poli_aktif; ?></div>
        </div>
        <div class="kpi-icon">🏛️</div>
    </div>
</div>

    <section class="admin-section">
        <h1>Tabel Manajemen Antrian</h1>
        <p>Gunakan tombol "Panggil Berikutnya" untuk mengelola alur antrian di setiap poli.</p>
        
        <div id="admin-dashboard" class="admin-dashboard">
            <p>Memuat data antrian...</p>
        </div>
    </section>
</main>

<footer id="contact">
    <div class="footer-bottom">
        <p>Antricare© 2024 by Kelompok 6</p>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>