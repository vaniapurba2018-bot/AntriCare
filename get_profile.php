<?php

session_start();
include 'db.php'; 


if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

try {
   
    $stmt = $pdo->prepare("SELECT poli, nomor_antrian, status, timestamp, nik FROM queues 
                           WHERE user_id = ? AND status IN ('done', 'skipped')
                           ORDER BY timestamp DESC");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll();

   
    $stmt = $pdo->prepare("SELECT poli, nomor_antrian, nik, status FROM queues 
                           WHERE user_id = ? AND status IN ('waiting', 'serving')
                           ORDER BY timestamp DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $active_ticket = $stmt->fetch(); 


    echo json_encode([
        'success' => true,
        'user' => [
            'name' => $user_name,
            'email' => $user_email
        ],
        'active_ticket' => $active_ticket, 
        'history' => $history 
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>