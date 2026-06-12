<?php

session_start();
include 'db.php'; 


if (!isset($_SESSION['user_id'])) {
    die('Akses ditolak.');
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    if (isset($_POST['action']) && $_POST['action'] === 'tambah') {
        
        $poli = $_POST['poli_nama'];
        $hari = $_POST['hari'];
        $jam_buka = $_POST['jam_buka'];
        $jam_tutup = $_POST['jam_tutup'];
        $dokter = $_POST['dokter_bertugas'];

        try {
            $stmt = $pdo->prepare("INSERT INTO jadwal_poli (poli_nama, hari, jam_buka, jam_tutup, dokter_bertugas) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$poli, $hari, $jam_buka, $jam_tutup, $dokter]);
        } catch (PDOException $e) {
            die("Error saat menambah data: ". $e->getMessage());
    }
}
    
    
    else if (isset($_POST['action']) && $_POST['action'] === 'hapus') {
        
        $id_jadwal = $_POST['id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM jadwal_poli WHERE id = ?");
            $stmt->execute([$id_jadwal]);
        } catch (PDOException $e) {
            die("Error saat menghapus data: ". $e->getMessage());
        }
    }

    header('Location: kelola_jadwal.php');
    exit;

} else {
   
    header('Location: kelola_jadwal.php');
    exit;
}
?>