<?php
// Script Import Data Antrian CSV
include 'db.php';

echo "<h2>Proses Import Data CSV ke Database...</h2>";

try {
    // 1. Sesuaikan Struktur Tabel (Otomatis menambahkan kolom dokter jika belum ada)
    // dan memastikan user_id boleh kosong (NULL) karena data fiktif ini tidak terikat ke akun login
    $pdo->exec("ALTER TABLE queues ADD COLUMN IF NOT EXISTS dokter VARCHAR(100) NULL");
    $pdo->exec("ALTER TABLE queues MODIFY user_id INT NULL");
    
    // 2. Membaca file CSV
    $file = 'data_antrian.csv';
    
    if (!file_exists($file)) {
        die("<b>Error:</b> File '$file' tidak ditemukan! Pastikan file CSV ada di folder yang sama.");
    }

    $handle = fopen($file, "r");
    
    // Lewati baris pertama (Header CSV)
    fgetcsv($handle, 1000, ","); 
    
    // Siapkan perintah SQL
    // Karena kita tidak tahu apakah ID di CSV bentrok dengan yang ada di DB, kita gunakan INSERT IGNORE
    $sql = "INSERT IGNORE INTO queues (id, user_id, poli, nomor_antrian, nama_pasien, nik, status, timestamp, dokter) 
            VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    $count = 0;
    
    // 3. Looping dan masukkan data baris demi baris
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        // Indeks array sesuai dengan urutan kolom di CSV Anda
        // [0]id, [1]poli, [2]nomor_antrian, [3]nama_pasien, [4]nik, [5]status, [6]timestamp, [7]dokter
        $stmt->execute([
            $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6], $data[7]
        ]);
        $count++;
    }
    
    fclose($handle);
    
    echo "<h3 style='color: green;'>Sukses! Berhasil mengimpor $count data antrian fiktif.</h3>";
    echo "<p>Silakan buka <a href='laporan.php'>Dashboard Laporan</a> untuk melihat hasilnya.</p>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Terjadi kesalahan Database: " . $e->getMessage() . "</h3>";
}
?>