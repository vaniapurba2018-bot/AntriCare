<?php


session_start();
include 'db.php'; 


if (!isset($_SESSION['user_id'])) { 
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit;
}


$action = $_GET['action'] ?? '';
$poli = $_GET['poli'] ?? '';


if ($action == 'call_next' && !empty($poli)) {
    try {
       
        $pdo->beginTransaction();

      
        $stmt = $pdo->prepare("UPDATE queues SET status = 'done' 
                               WHERE poli = ? AND status = 'serving' AND DATE(timestamp) = CURDATE()");
        $stmt->execute([$poli]);

   
        $stmt = $pdo->prepare("SELECT id FROM queues 
                               WHERE poli = ? AND status = 'waiting' AND DATE(timestamp) = CURDATE() 
                               ORDER BY timestamp ASC LIMIT 1");
        $stmt->execute([$poli]);
        $next_patient = $stmt->fetch();

        if ($next_patient) {
           
            $stmt = $pdo->prepare("UPDATE queues SET status = 'serving' WHERE id = ?");
            $stmt->execute([$next_patient['id']]);
            $message = 'Pasien berikutnya berhasil dipanggil';
        } else {
         
            $message = 'Tidak ada antrian lagi di poli ini';
        }

 
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (PDOException $e) {
        $pdo->rollBack(); 
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid']);
}


?>