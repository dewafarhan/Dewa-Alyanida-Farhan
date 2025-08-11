<?php
require_once 'config.php';
require_once 'auth.php';

// Cek apakah user sudah login
requireLogin();
$user = getCurrentUser();

if (isset($_GET['id'])) {
    try {
        $id = $_GET['id'];
        
        // Cek apakah tugas ada dan milik user yang sedang login
        $sql = "SELECT nama_tugas FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        $task = $stmt->fetch();
        
        if (!$task) {
            throw new Exception("Tugas tidak ditemukan atau bukan milik Anda!");
        }
        
        // Hapus tugas
        $sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $user['id']]);
        
        header("Location: index.php?success=Tugas '{$task['nama_tugas']}' berhasil dihapus!");
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