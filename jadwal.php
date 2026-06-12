<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header('Location: login.html?redirect_url=' . $redirect_url);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Layanan - AntriCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <nav class="navbar">
        <div class="logo">
            <img src="images/logo.png" alt="Logo AntriCare" class="logo-img">
            <span>AntriCare</span>
        </div>
        <ul class="nav-links">
            <li><a href="index.html">HOME</a></li>
            <li><a href="about.html">ABOUT US</a></li>
            <li class="dropdown">
                <a href="javascript:void(0);" class="dropbtn">POLI &#9662;</a>
                <div class="dropdown-content" id="poliDropdown">
                    <a href="poli.html#umum">UMUM</a>
                    <a href="poli.html#gigi">MULUT DAN GIGI</a>
                    <a href="poli.html#kia">KANDUNGAN DAN ANAK</a>
                    <a href="poli.html#kb">KELUARGA BERENCANA</a>
                    <a href="poli.html#gizi">POLI GIZI</a>
                    <a href="poli.html#lansia">LANSIA</a>
                </div>
            </li>
            <li><a href="daftar-antrian.php">ANTRIAN</a></li>
            <li><a href="jadwal.php">JADWAL</a></li> <li><a href="profil.php">PROFIL</a></li>
            <?php
 
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {

       
        echo '<li><a href="admin.php" style="color: #FFD700; font-weight: bold; border: 1px solid #FFD700; border-radius: 5px; padding: 5px 10px;">ADMIN PANEL</a></li>';
    }
    ?>

</ul>
        </ul>
        <div class="nav-buttons" id="nav-auth">
            <a href="login.html" class="btn btn-login">LOG IN</a>
        </div>
    </nav>
</header>

<main class="page-container">
    <section class="queue-list-section">
        <h1>Jadwal Layanan & Dokter</h1>
        <p>Jadwal ini berlaku untuk hari ini, 
            <strong><span id="nama-hari-ini">...</span></strong>. 
            Anda hanya dapat mengambil antrian untuk poli yang berstatus "Buka".
        </p>

        <table class="queue-table" id="jadwal-table">
            <thead>
                <tr>
                    <th>Poli Layanan</th>
                    <th>Dokter Bertugas</th>
                    <th>Jam Operasional</th>
                    <th>Status</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody id="jadwal-table-body">
                <tr id="jadwal-loading">
                    <td colspan="5" style="text-align: center;">Memuat jadwal...</td>
                </tr>
                </tbody>
        </table>
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