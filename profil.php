<?php
session_start();
// include 'db.php'; // Tidak perlu

// --- PENGAMANAN HALAMAN PENGGUNA ---
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
    <title>Profil Pengguna - AntriCare</title>
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
            <li><a href="jadwal.php">JADWAL</a></li>
            <li><a href="profil.php">PROFIL</a></li>

            <?php
    // --- TAMBAHKAN BLOK KODE INI ---
    // Cek apakah peran 'admin' ada di session
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {

        // Jika ya, tampilkan link khusus untuk kembali ke panel admin
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
    <div class="userprofil">
   <h1 class="userprofil">Profil Pengguna</h1>

    <section class="profile-card active-queue-card">
        <h2>Antrian Aktif Anda</h2>
        <div id="active-queue-content">
            <p id="active-queue-loading">Memeriksa antrian...</p>
            
            <div id="active-queue-details" style="display: none;">
                <p>Anda memiliki 1 antrian di:</p>
                <h3 id="active-poli">(Poli)</h3>
                <div class="ticket-number-large" id="active-nomor">(Nomor)</div>
                <div class="ticket-actions">
                    <a href="nomor.html" class="btn-ticket">Lihat Detail Tiket</a>
                    </div>
            </div>

            <div id="no-active-queue" style="display: none;">
                <p>Anda tidak memiliki antrian aktif saat ini.</p>
                <a href="jadwal.php" class="btn btn-primary">Lihat Jadwal & Ambil Antrian</a>
            </div>
        </div>
    </section>

    <section class="profile-card">
        <h2>Data Diri</h2>
        <form id="profile-form">
            <div class="input-group">
                <label for="profile-name">Nama</label>
                <input type="text" id="profile-name" disabled>
            </div>
            <div class="input-group">
                <label for="profile-email">Email</label>
                <input type="email" id="profile-email" disabled>
            </div>
            <div class="input-group">
                <label for="profile-nik">NIK</label>
                <input type="text" id="profile-nik" placeholder="(Data NIK dari kunjungan terakhir)" disabled>
            </div>
            
            <h3 class="form-divider">Ubah Password</h3>
            <p style="color: #666; margin-bottom: 15px;">Fitur ini belum tersedia.</p>
            
            <button type="submit" class="btn btn-primary" disabled>
                <span class="btn-text">Simpan Perubahan</span>
            </button>
        </form>
    </section>

    <section class="profile-card">
        <h2>Riwayat Kunjungan</h2>
        <table class="queue-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Poli Tujuan</th>
                    <th>Nomor Antrian</th>
                </tr>
            </thead>
            <tbody id="history-table-body">
                <tr id="history-loading-row">
                    <td colspan="3" style="text-align: center;">Memuat riwayat...</td>
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
