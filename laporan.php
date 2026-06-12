<?php
session_start();

// Cek sesi login & role admin
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

// Filter Tanggal (Default: 30 hari terakhir)
$date_start = $_GET['date_start'] ?? date('Y-m-d', strtotime('-30 days'));
$date_end = $_GET['date_end'] ?? date('Y-m-d');

// --- QUERY 1: Data untuk Grafik Bar Horizontal (Count of AntrianID by Jam) ---
$stmt = $pdo->prepare("SELECT HOUR(timestamp) as jam, COUNT(*) as jumlah 
                       FROM queues 
                       WHERE DATE(timestamp) BETWEEN ? AND ? 
                       GROUP BY jam 
                       ORDER BY jam ASC");
$stmt->execute([$date_start, $date_end]);
$kunjungan_jam = $stmt->fetchAll();

$jam_labels = [];
$jam_data = [];
foreach ($kunjungan_jam as $row) {
    $jam_labels[] = $row['jam'];
    $jam_data[] = $row['jumlah'];
}
$jam_labels_json = json_encode($jam_labels);
$jam_data_json = json_encode($jam_data);

// --- QUERY 2: Data untuk Grafik Garis (Count of AntrianID by Tanggal) ---
$stmt = $pdo->prepare("SELECT DATE(timestamp) as tanggal, COUNT(*) as jumlah 
                       FROM queues 
                       WHERE DATE(timestamp) BETWEEN ? AND ? 
                       GROUP BY tanggal 
                       ORDER BY tanggal ASC");
$stmt->execute([$date_start, $date_end]);
$kunjungan_tanggal = $stmt->fetchAll();

$tanggal_labels = [];
$tanggal_data = [];
foreach ($kunjungan_tanggal as $row) {
    $tanggal_labels[] = date('d M Y', strtotime($row['tanggal'])); 
    $tanggal_data[] = $row['jumlah'];
}
$tanggal_labels_json = json_encode($tanggal_labels);
$tanggal_data_json = json_encode($tanggal_data);

// --- QUERY 3: Data untuk Grafik Pie (Count of AntrianID by Poli) ---
$stmt = $pdo->prepare("SELECT poli, COUNT(*) as jumlah 
                       FROM queues 
                       WHERE DATE(timestamp) BETWEEN ? AND ? 
                       GROUP BY poli");
$stmt->execute([$date_start, $date_end]);
$kunjungan_poli = $stmt->fetchAll();

$poli_labels = [];
$poli_data = [];
foreach ($kunjungan_poli as $row) {
    $poli_labels[] = $row['poli'];
    $poli_data[] = $row['jumlah'];
}
$poli_labels_json = json_encode($poli_labels);
$poli_data_json = json_encode($poli_data);

// --- QUERY 4: Rata-rata Waktu Tunggu per Dokter (Simulasi) ---
$stmt = $pdo->prepare("SELECT dokter, ROUND(AVG(15 + (id MOD 30))) as waktu_tunggu 
                       FROM queues 
                       WHERE dokter IS NOT NULL AND dokter != '' AND DATE(timestamp) BETWEEN ? AND ? 
                       GROUP BY dokter 
                       ORDER BY waktu_tunggu DESC LIMIT 5");
$stmt->execute([$date_start, $date_end]);
$waktu_dokter = $stmt->fetchAll();

$dokter_labels = [];
$dokter_data = [];
foreach ($waktu_dokter as $row) {
    $dokter_labels[] = $row['dokter']; 
    $dokter_data[] = $row['waktu_tunggu'];
}
$dokter_labels_json = json_encode($dokter_labels);
$dokter_data_json = json_encode($dokter_data);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Laporan - AntriCare</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <form method="GET" action="laporan.php" style="display: flex; gap: 10px; align-items: center; justify-content: flex-end; margin-bottom: 10px;">
        <span style="font-size: 14px;">Filter Tanggal:</span>
        <input type="date" name="date_start" value="<?php echo htmlspecialchars($date_start); ?>" style="padding: 5px;">
        <span>-</span>
        <input type="date" name="date_end" value="<?php echo htmlspecialchars($date_end); ?>" style="padding: 5px;">
        <button type="submit" class="btn-primary" style="padding: 5px 15px; border:none; cursor:pointer;">Filter</button>
    </form>

    <div class="pbi-container">
        <header class="pbi-header">
            <h1>DASHBOARD ANTRIAN PUSKESMAS</h1>
            <img src="images/logo.png" alt="Antri Care Medical Clinic">
        </header>

        <div class="pbi-body">
            <aside class="pbi-slicer">
                <h3>NamaPoli</h3>
                <div class="checkbox-group">
                    <label class="checkbox-label"><input type="checkbox" value="blank"> (Blank)</label>
                    <label class="checkbox-label"><input type="checkbox" value="Poli Gigi"> Poli Gigi</label>
                    <label class="checkbox-label"><input type="checkbox" value="Poli Gizi" checked> Poli Gizi</label>
                    <label class="checkbox-label"><input type="checkbox" value="Poli KB"> Poli KB</label>
                    <label class="checkbox-label"><input type="checkbox" value="Poli KIA"> Poli KIA</label>
                    <label class="checkbox-label"><input type="checkbox" value="Poli Lansia"> Poli Lansia</label>
                    <label class="checkbox-label"><input type="checkbox" value="Poli Umum"> Poli Umum</label>
                </div>
            </aside>

            <div class="pbi-visuals">
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div class="pbi-chart-container top-chart">
                        <div class="chart-title">Count of Antrian by Jam</div>
                        <canvas id="chartJam"></canvas>
                    </div>

                    <div class="pbi-chart-container top-chart">
                        <div class="chart-title">Distribusi per Poli</div>
                        <canvas id="piePoli"></canvas>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div class="pbi-chart-container bottom-chart">
                        <div class="chart-title">Count of Antrian by Tanggal</div>
                        <canvas id="chartTanggal"></canvas>
                    </div>
                    
                    <div class="pbi-chart-container bottom-chart">
                        <div class="chart-title">Rata-rata Waktu Tunggu (Menit)</div>
                        <canvas id="chartDokter"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer id="contact">
    <div class="footer-bottom">
        <p>Antricare© 2024 by Kelompok 6</p>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const powerBiPurple = '#735bc1'; 
    const gridDashConfig = [3, 3]; 

    // Menerima data JSON dari PHP
    const jamLabels = <?php echo $jam_labels_json; ?>;
    const jamData = <?php echo $jam_data_json; ?>;
    
    const tanggalLabels = <?php echo $tanggal_labels_json; ?>;
    const tanggalData = <?php echo $tanggal_data_json; ?>;

    const poliLabels = <?php echo $poli_labels_json; ?>;
    const poliData = <?php echo $poli_data_json; ?>;

    const dokterLabels = <?php echo $dokter_labels_json; ?>;
    const dokterData = <?php echo $dokter_data_json; ?>;

    // 1. GRAFIK ATAS KIRI (Horizontal Bar Chart - Jam)
    const ctxJam = document.getElementById('chartJam');
    if (ctxJam) {
        new Chart(ctxJam.getContext('2d'), {
            type: 'bar',
            data: {
                labels: jamLabels,
                datasets: [{
                    data: jamData,
                    backgroundColor: powerBiPurple,
                    barThickness: 18
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Count of AntrianID' },
                        grid: { drawBorder: false, color: '#eeeeee', borderDash: gridDashConfig }
                    },
                    y: {
                        title: { display: true, text: 'Jam' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. GRAFIK ATAS KANAN (Doughnut Chart - Poli)
    const ctxPoli = document.getElementById('piePoli');
    if (ctxPoli) {
        new Chart(ctxPoli.getContext('2d'), {
            type: 'doughnut', 
            data: {
                labels: poliLabels,
                datasets: [{
                    data: poliData,
                    backgroundColor: [
                        '#735bc1', '#0078d4', '#5c9edd', '#107c41', '#ffc107', '#d9534f'
                    ],
                    borderWidth: 1,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let dataset = context.chart.data.datasets[0].data;
                                let total = dataset.reduce((a, b) => Number(a) + Number(b), 0);
                                let percentage = ((context.raw / total) * 100).toFixed(1) + "%";
                                return " " + context.label + ": " + context.raw + " Pasien (" + percentage + ")";
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. GRAFIK BAWAH KIRI (Line Chart Patah-Patah - Tanggal)
    const ctxTanggal = document.getElementById('chartTanggal');
    if (ctxTanggal) {
        new Chart(ctxTanggal.getContext('2d'), {
            type: 'line',
            data: {
                labels: tanggalLabels,
                datasets: [{
                    data: tanggalData,
                    borderColor: powerBiPurple,
                    borderWidth: 2.5,
                    fill: false,
                    tension: 0, 
                    pointRadius: 0, 
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Tanggal' },
                        grid: { display: false }
                    },
                    y: {
                        title: { display: true, text: 'Count of AntrianID' },
                        grid: { drawBorder: false, color: '#eeeeee', borderDash: gridDashConfig },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // 4. GRAFIK BAWAH KANAN (Horizontal Bar Chart - Dokter)
    const ctxDokter = document.getElementById('chartDokter');
    if (ctxDokter) {
        new Chart(ctxDokter.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dokterLabels,
                datasets: [{
                    data: dokterData,
                    backgroundColor: '#107c41', 
                    barThickness: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y', 
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return " Rata-rata: " + context.raw + " Menit";
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: { display: true, text: 'Menit' },
                        grid: { drawBorder: false, color: '#eeeeee', borderDash: gridDashConfig },
                        beginAtZero: true
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Logika Navbar Logout
    const navAuth = document.getElementById('nav-auth');
    if (navAuth && localStorage.getItem('loggedInUser')) {
        const user = JSON.parse(localStorage.getItem('loggedInUser'));
        navAuth.innerHTML = `
            <span class="welcome-user">Halo, ${user.name}!</span>
            <a href="#" id="logoutButton" class="btn btn-login">Logout</a>
        `;
        
        const logoutButton = document.getElementById('logoutButton');
        if (logoutButton) {
            logoutButton.addEventListener('click', (e) => {
                e.preventDefault();
                fetch('logout.php').then(() => {
                    localStorage.removeItem('loggedInUser');
                    window.location.href = 'login.html';
                });
            });
        }
    }

});
</script>
</body>
</html>