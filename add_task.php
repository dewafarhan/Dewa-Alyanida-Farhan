<?php
require_once 'config.php';
require_once 'auth.php';

// Cek apakah user sudah login
requireLogin();
$user = getCurrentUser();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nama_tugas = trim($_POST['nama_tugas']);
        $prioritas = $_POST['prioritas'];
        $tanggal = $_POST['tanggal'];
        $deadline = $_POST['deadline'];
        
        // Validasi input
        if (empty($nama_tugas) || empty($tanggal) || empty($deadline)) {
            throw new Exception("Semua field harus diisi!");
        }
        
        if ($deadline < $tanggal) {
            throw new Exception("Deadline tidak boleh lebih awal dari tanggal tugas!");
        }
        
        $sql = "INSERT INTO tasks (user_id, nama_tugas, prioritas, tanggal, deadline) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id'], $nama_tugas, $prioritas, $tanggal, $deadline]);
        
        header("Location: index.php?success=Tugas berhasil ditambahkan!");
        exit();
        
    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>