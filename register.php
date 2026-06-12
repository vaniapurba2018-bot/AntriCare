<?php

include 'db.php';


$data = json_decode(file_get_contents('php://input'), true);


if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Semua kolom harus diisi']);
    exit;
}

$name = $data['name'];
$email = $data['email'];
$password = $data['password'];

try {
  
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        
        echo json_encode(['success' => false, 'message' => 'Email ini sudah terdaftar']);
        exit;
    }

 
    $password_hash = password_hash($password, PASSWORD_BCRYPT);


    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password_hash]);


    echo json_encode(['success' => true, 'message' => 'Pendaftaran berhasil!']);

} catch (PDOException $e) {
   
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>