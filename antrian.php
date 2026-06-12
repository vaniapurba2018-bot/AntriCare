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
    <title>Ambil Antrian - AntriCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="form-page">
   
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

<main class="form-container-full">
    <div class="form-box-full">
        <h2>Ambil Antrian</h2>
        <p>Mohon lengkapi data pada form yang tertera untuk mengambil nomor antrian</p>
        <form id="antrianForm">
        
            <div class="form-row">
                <input type="text" id="nama" name="nama" placeholder="Nama Sesuai KTP" required>
                <input type="text" id="nik" name="nik" placeholder="NIK (16 Digit)" maxlength="16" pattern="[0-9]{16}" oninput="this.value=this.value.replace(/[^0-j0-9]/g,'')" title="NIK harus terdiri dari 16 digit angka" required>
            </div>
            <div class="form-row">
                <input type="text" id="alamat" name="alamat" placeholder="Alamat (Opsional)">
                <input type="date" id="tanggal" name="tanggal" required>
            </div>
            <div class="form-row">
                <select id="poli" name="poli" required>
                    <option value="" disabled selected>Pilih Poli</option>
                    <option value="Poli Umum">Poli Umum</option>
                    <option value="Poli Gigi">Poli Gigi</option>
                    <option value="Poli KIA">Poli KIA</option>
                    <option value="Poli KB">Poli KB</option>
                    <option value="Poli Lansia">Poli Lansia</option>
                    <option value="Poli Gizi">Poli Gizi</option>
                </select>
            </div>
            <textarea name="message" placeholder="Pesan (Opsional)"></textarea>
            
            <button type="submit" class="btn btn-primary">Ambil Nomor Antrian</button>
        </form>
    </div>
</main>
<script src="script.js"></script>
</body>
</html>