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

// --- LOGIKA PREDIKSI (SIMPLE MOVING AVERAGE) ---

// 1. Ambil tanggal terakhir yang ada datanya di database (karena data CSV ada di tahun 2025)
$stmt = $pdo->query("SELECT MAX(DATE(timestamp)) FROM queues");
$last_date_db = $stmt->fetchColumn();

if (!$last_date_db) {
    $last_date_db = date('Y-m-d'); // Fallback jika database kosong
}

// 2. Ambil data historis 30 hari terakhir dari tanggal terakhir tersebut
$stmt = $pdo->prepare("SELECT DATE(timestamp) as tanggal, COUNT(*) as jumlah 
                       FROM queues 
                       WHERE DATE(timestamp) <= ? 
                       GROUP BY tanggal 
                       ORDER BY tanggal DESC LIMIT 30");
$stmt->execute([$last_date_db]);
$history_raw = $stmt->fetchAll();

// Balik urutan menjadi kronologis (lama ke baru)
$history_raw = array_reverse($history_raw);

$labels = [];
$history_data = [];
$prediction_data = [];

// Pisahkan label dan data historis
foreach ($history_raw as $row) {
    $labels[] = date('d M Y', strtotime($row['tanggal']));
    $history_data[] = $row['jumlah'];
    $prediction_data[] = null; // Kosongkan data prediksi untuk titik historis
}

// 3. Prediksi 7 hari ke depan menggunakan Simple Moving Average (7-Hari)
$window = 7;
$data_points = $history_data;
$last_historical_value = end($history_data);

// Hubungkan garis grafik: titik terakhir history menjadi titik awal prediksi
$prediction_data[count($prediction_data) - 1] = $last_historical_value; 

$besok_prediksi = 0;

for ($i = 1; $i <= 7; $i++) {
    // Ambil sejumlah $window data terakhir
    $slice = array_slice($data_points, -$window);
    
    // Hitung rata-rata
    $rata_rata = array_sum($slice) / count($slice);
    $prediksi_nilai = round($rata_rata); // Bulatkan karena jumlah orang tidak mungkin desimal
    
    // Simpan nilai prediksi untuk digunakan pada iterasi berikutnya
    $data_points[] = $prediksi_nilai;
    
    // Tambahkan label tanggal baru
    $next_date = date('d M Y', strtotime($last_date_db . " +$i days"));
    $labels[] = $next_date;
    
    // Kosongkan data historis untuk masa depan, isi data prediksi
    $history_data[] = null;
    $prediction_data[] = $prediksi_nilai;

    if ($i == 1) {
        $besok_prediksi = $prediksi_nilai; // Simpan prediksi untuk besok (H+1)
    }
}

// Data untuk dikirim ke JavaScript
$labels_json = json_encode($labels);
$history_json = json_encode($history_data);
$prediction_json = json_encode($prediction_data);

// --- Prediksi Poli Tersibuk Besok (Berdasarkan Tren 7 Hari Terakhir) ---
$stmt = $pdo->prepare("SELECT poli, COUNT(*) as jumlah FROM queues 
                       WHERE DATE(timestamp) BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND ?
                       GROUP BY poli ORDER BY jumlah DESC LIMIT 1");
$stmt->execute([$last_date_db, $last_date_db]);
$poli_prediksi = $stmt->fetch();
$nama_poli_prediksi = $poli_prediksi ? $poli_prediksi['poli'] : 'N/A';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Antrian - AntriCare</title>
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
    <div class="pbi-container">
        <header class="pbi-header">
            <h1>FORECASTING & PREDIKSI KUNJUNGAN</h1>
            <img src="images/logo.png" alt="Antri Care Medical Clinic">
        </header>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px;">
            <div class="kpi-card" style="border-top: 4px solid #ff9800; background-color: #fffaf0;">
                <span class="kpi-title">Prediksi Kunjungan Besok</span>
                <span class="kpi-value" style="color: #ff9800;"><?php echo $besok_prediksi; ?> <small>Pasien</small></span>
            </div>
            <div class="kpi-card" style="border-top: 4px solid #f44336; background-color: #fff5f5;">
                <span class="kpi-title">Estimasi Poli Tersibuk Besok</span>
                <span class="kpi-value" style="color: #f44336; font-size: 20px;"><?php echo htmlspecialchars($nama_poli_prediksi); ?></span>
            </div>
            <div class="kpi-card" style="border-top: 4px solid #4caf50;">
                <span class="kpi-title">Status Kapasitas Klinik</span>
                <span class="kpi-value" style="color: #4caf50; font-size: 20px;">Aman (Terkendali)</span>
            </div>
        </div>

        <div class="pbi-visuals">
            <div class="pbi-chart-container" style="height: 400px;">
                <div class="chart-title">Grafik Data Historis vs Prediksi (7 Hari Kedepan)</div>
                <canvas id="prediksiChart"></canvas>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f3f2f1; border-radius: 5px; font-size: 13px; color: #555;">
            <strong>ℹ️ Metode Peramalan:</strong> Sistem menggunakan algoritma <em>Simple Moving Average (SMA)</em> berbasis data 7 hari ke belakang untuk memproyeksikan volume kunjungan. Garis padat ungu menunjukkan data aktual, sedangkan garis putus-putus oranye menunjukkan proyeksi prediksi.
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const labels = <?php echo $labels_json; ?>;
    const historyData = <?php echo $history_json; ?>;
    const predictionData = <?php echo $prediction_json; ?>;

    const ctxPrediksi = document.getElementById('prediksiChart');
    if (ctxPrediksi) {
        new Chart(ctxPrediksi.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Data Aktual (Historis)',
                        data: historyData,
                        borderColor: '#735bc1', // Ungu Power BI
                        backgroundColor: 'rgba(115, 91, 193, 0.1)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.3, // Sedikit melengkung
                        pointRadius: 3
                    },
                    {
                        label: 'Prediksi (Forecasting)',
                        data: predictionData,
                        borderColor: '#ff9800', // Oranye untuk prediksi
                        borderWidth: 2.5,
                        borderDash: [5, 5], // Membuat garis menjadi putus-putus
                        fill: false,
                        tension: 0.3,
                        pointBackgroundColor: '#ff9800',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        title: { display: true, text: 'Jumlah Pasien' },
                        grid: { color: '#eeeeee', borderDash: [3, 3] },
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>

</body>
</html>