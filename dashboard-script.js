// --- 2. TREN WAKTU (Line Chart - Tren Pendaftar) ---
const ctxTrend = document.getElementById('trendChart').getContext('2d');
const trendChart = new Chart(ctxTrend, {
    type: 'line',
    data: {
        labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
        datasets: [{
            label: 'Pendaftar Baru',
            data: [10, 25, 18, 35, 42, 30, 48],
            borderColor: '#0078d4', // Warna tema Power BI
            backgroundColor: 'rgba(0, 120, 212, 0.1)',
            tension: 0.3, // Membuat garis sedikit melengkung halus
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false } // Sembunyikan label kotak atas jika ingin clean
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// --- 3. PERBANDINGAN KATEGORI (Bar Chart - Pendaftar Per Kota) ---
const ctxCity = document.getElementById('cityChart').getContext('2d');
const cityChart = new Chart(ctxCity, {
    type: 'bar',
    data: {
        labels: ['Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang'],
        datasets: [{
            label: 'Jumlah Pengguna',
            data: [450, 320, 210, 150, 104],
            backgroundColor: [
                '#0078d4', // Bar Utama
                '#2b88d8', 
                '#5c9edd', 
                '#8cb3e2', 
                '#b9ceeb'  // Gradasi makin kecil makin terang
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y', // Mengubah chart menjadi horizontal bar chart ala dashboard eksekutif
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});

// --- Integrasi Interaksi Filter (Slicer) Sederhana ---
document.querySelector('.btn-apply').addEventListener('click', function() {
    const selectedCity = document.getElementById('filter-city').value;
    const selectedStatus = document.getElementById('filter-status').value;

    alert(`Memfilter data untuk Kota: ${selectedCity.toUpperCase()} dan Status: ${selectedStatus.toUpperCase()}`);
    
    // Di sini Anda bisa memanipulasi data grafik secara real-time, contoh:
    // cityChart.data.datasets[0].data = [500, 200, 100, 50, 30];
    // cityChart.update();
});