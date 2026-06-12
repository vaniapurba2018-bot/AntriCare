<?php


session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}


date_default_timezone_set('Asia/Jakarta');


$master_poli = [
    "Poli Umum", "Poli Gigi", "Poli KIA", 
    "Poli KB", "Poli Gizi", "Poli Lansia"
];


$hari_ini = date('N'); 
$waktu_sekarang = date('H:i:s'); 

try {
    
    $stmt = $pdo->prepare("SELECT * FROM jadwal_poli WHERE hari = ?");
    $stmt->execute([$hari_ini]);
    $jadwal_db = $stmt->fetchAll();
    
    
    $jadwal_hari_ini = [];
    foreach ($jadwal_db as $j) {
        $jadwal_hari_ini[$j['poli_nama']] = $j;
    }

    $hasil_final = [];
    
    
    foreach ($master_poli as $nama_poli) {
        if (isset($jadwal_hari_ini[$nama_poli])) {
           
            $data = $jadwal_hari_ini[$nama_poli];
            
            $status = "Tutup"; 
            
           
            if ($waktu_sekarang >= $data['jam_buka'] && $waktu_sekarang <= $data['jam_tutup']) {
                $status = "Buka";
            }
            
            $hasil_final[] = [
                'poli' => $nama_poli,
                'dokter' => $data['dokter_bertugas'],
                'jam_buka' => date('H:i', strtotime($data['jam_buka'])), 
                'jam_tutup' => date('H:i', strtotime($data['jam_tutup'])),
                'status' => $status
            ];
            
        } else {
          
            $hasil_final[] = [
                'poli' => $nama_poli,
                'dokter' => '-',
                'jam_buka' => 'Tutup',
                'jam_tutup' => '',
                'status' => 'Tutup'
            ];
        }
    }

  
    echo json_encode(['success' => true, 'jadwal' => $hasil_final]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>