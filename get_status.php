<?php


session_start();
include 'db.php'; 


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

try {
  
    $stmt = $pdo->prepare("SELECT id, poli, nomor_antrian, nama_pasien, status 
                           FROM queues 
                           WHERE status IN ('waiting', 'serving') AND DATE(timestamp) = CURDATE() 
                           ORDER BY timestamp ASC");
    $stmt->execute();
    $queues = $stmt->fetchAll();

 
    $stmt = $pdo->prepare("SELECT poli, nomor_antrian, nama_pasien 
                           FROM queues 
                           WHERE status = 'serving' AND DATE(timestamp) = CURDATE()");
    $stmt->execute();
    $serving_raw = $stmt->fetchAll();
 
    $serving = [];
    foreach ($serving_raw as $item) {
        $serving[$item['poli']] = $item;
    }

   
    echo json_encode([
        'success' => true, 
        'queues' => $queues,    
        'serving' => $serving   
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>