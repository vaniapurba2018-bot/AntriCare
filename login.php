<?php

session_start();


include 'db.php';


$data = json_decode(file_get_contents('php://input'), true);


if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Email dan password harus diisi']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
  
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

 
    if ($user && password_verify($password, $user['password_hash'])) {
        
      
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role']; 

  
    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil!',
        'user' => [
            'name' => $user['name'],
            'role' => $user['role']  
        ]
    ]);


    } else {
       
        echo json_encode(['success' => false, 'message' => 'Email atau password salah']);
    }

} catch (PDOException $e) {
 
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>