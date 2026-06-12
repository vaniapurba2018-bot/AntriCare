<?php

session_start();
include 'db.php'; 


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login.']);
    exit;
}


$user_id = $_SESSION['user_id'];
$nama = $_POST['nama'] ?? '';
$nik = $_POST['nik'] ?? '';
$poli = $_POST['poli'] ?? '';


if (empty($nama) || empty($nik) || empty($poli)) {
    echo json_encode(['success' => false, 'message' => 'Nama, NIK, dan Poli harus diisi']);
    exit;
}
if (strlen($nik) != 16 || !ctype_digit($nik)) {
    echo json_encode(['success' => false, 'message' => 'NIK harus terdiri dari 16 digit angka']);
    exit;
}

try {
    
    $stmt = $pdo->prepare("SELECT id FROM queues WHERE user_id = ? AND status IN ('waiting', 'serving') AND DATE(timestamp) = CURDATE()");
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Anda sudah memiliki 1 nomor antrian aktif.']);
        exit;
    }

   
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM queues WHERE poli = ? AND DATE(timestamp) = CURDATE()");
    $stmt->execute([$poli]);
    $nomor_urut = $stmt->fetchColumn() + 1;

    $kode_poli_array = explode(' ', $poli);
    $kode_poli = strtoupper(substr(end($kode_poli_array), 0, 4));
    $nomor_antrian = sprintf('%s-%03d', $kode_poli, $nomor_urut);

 
    $stmt = $pdo->prepare("INSERT INTO queues (user_id, poli, nomor_antrian, nama_pasien, nik, status) 
                           VALUES (?, ?, ?, ?, ?, 'waiting')");
    $stmt->execute([$user_id, $poli, $nomor_antrian, $nama, $nik]);

  
    echo json_encode([
        'success' => true,
        'message' => 'Nomor antrian berhasil diambil!',
        'ticketData' => [
            'nomor' => $nomor_antrian,
            'nama' => $nama,
            'poli' => $poli,
            'jam' => date('H:i') 
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>