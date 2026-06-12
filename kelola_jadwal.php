<?php
session_start();

if (!isset($_SESSION['user_id'])) {

    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header('Location: login.html?redirect_url=' . $redirect_url);
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
  
    header('Location: index.html'); 
    exit;
}


include 'db.php'; 



try {
    $stmt = $pdo->query("SELECT * FROM jadwal_poli ORDER BY hari, jam_buka");
    $semua_jadwal = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data jadwal: " . $e->getMessage());
}


$nama_hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Jadwal</title>
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

    <section class="profile-card">
        <h2>Tambah Jadwal Poli Baru</h2>
        
        <form action="action_jadwal.php" method="POST" id="jadwal-form">
            <input type="hidden" name="action" value="tambah">
            
            <div class="form-row">
                <select name="poli_nama" required>
                    <option value="">-- Pilih Poli --</option>
                    <option value="Poli Umum">Poli Umum</option>
                    <option value="Poli Gigi">Poli Gigi</option>
                    <option value="Poli KIA">Poli KIA</option>
                    <option value="Poli KB">Poli KB</option>
                    <option value="Poli Gizi">Poli Gizi</option>
                    <option value="Poli Lansia">Poli Lansia</option>
                </select>
                <select name="hari" required>
                    <option value="">-- Pilih Hari --</option>
                    <option value="1">Senin</option>
                    <option value="2">Selasa</option>
                    <option value="3">Rabu</option>
                    <option value="4">Kamis</option>
                    <option value="5">Jumat</option>
                    <option value="6">Sabtu</option>
                    <option value="7">Minggu</option>
                </select>
            </div>
            <div class="form-row">
                <input type="text" name="dokter_bertugas" placeholder="Nama Dokter" required>
            </div>
            <div class="form-row">
                <label>Jam Buka: <input type="time" name="jam_buka" required></label>
                <label>Jam Tutup: <input type="time" name="jam_tutup" required></label>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Jadwal</button>
        </form>
    </section>

    <section class="queue-list-section">
        <h2>Daftar Jadwal Aktif</h2>
        <table class="queue-table">
            <thead>
                <tr>
                    <th>Poli</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Dokter</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($semua_jadwal as $jadwal): ?>
                <tr>
                    <td><?php echo htmlspecialchars($jadwal['poli_nama']); ?></td>
                    <td><?php echo $nama_hari[$jadwal['hari']]; ?></td>
                    <td><?php echo date('H:i', strtotime($jadwal['jam_buka'])) . ' - ' . date('H:i', strtotime($jadwal['jam_tutup'])); ?></td>
                    <td><?php echo htmlspecialchars($jadwal['dokter_bertugas']); ?></td>
                    <td>
                        <form action="action_jadwal.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="<?php echo $jadwal['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus jadwal ini?');">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if (empty($semua_jadwal)): ?>
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada jadwal yang diinput.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

</main>

<footer id="contact">
    <div class="footer-bottom">
        <p>Antricare© 2024 by Kelompok 6</p>
    </div>
</footer>

</body>
</html>