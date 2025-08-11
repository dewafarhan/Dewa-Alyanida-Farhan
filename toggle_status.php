<?php
require_once 'config.php';
require_once 'auth.php';

// Cek apakah user sudah login
requireLogin();
$user = getCurrentUser();

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        
        // Ambil status saat ini dan pastikan tugas milik user yang login
        $sql = "SELECT status, nama_tugas FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        $task = $stmt->fetch();
        
        if (!$task) {
            throw new Exception("Tugas tidak ditemukan atau bukan milik Anda!");
        }
        
        // Toggle status
        $new_status = ($task['status'] == 'selesai') ? 'belum_selesai' : 'selesai';
        
        $sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $id, $user['id']]);
        
        $status_text = ($new_status == 'selesai') ? 'selesai' : 'belum selesai';
        header("Location: index.php?success=Status tugas '{$task['nama_tugas']}' diubah menjadi {$status_text}!");
        exit();
        
    } catch (Exception $e) {
        header("Location: index.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: index.php?error=ID tugas tidak valid!");
    exit();
}
?>