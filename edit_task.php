<?php
require_once 'config.php';
require_once 'auth.php';

// Cek apakah user sudah login
requireLogin();
$user = getCurrentUser();

// Ambil data tugas untuk diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM tasks WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id, $user['id']]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header("Location: index.php?error=Tugas tidak ditemukan atau bukan milik Anda!");
        exit();
    }
}

// Proses update tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
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
        
        // Pastikan tugas milik user yang sedang login
        $sql = "UPDATE tasks SET nama_tugas = ?, prioritas = ?, tanggal = ?, deadline = ? WHERE id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$nama_tugas, $prioritas, $tanggal, $deadline, $id, $user['id']]);
        
        if ($stmt->rowCount() == 0) {
            throw new Exception("Tugas tidak ditemukan atau bukan milik Anda!");
        }
        
        header("Location: index.php?success=Tugas berhasil diupdate!");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = $_POST['id'];
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
        
        $sql = "UPDATE tasks SET nama_tugas = ?, prioritas = ?, tanggal = ?, deadline = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_tugas, $prioritas, $tanggal, $deadline, $id]);
        
        header("Location: index.php?success=Tugas berhasil diupdate!");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas - To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Edit Tugas</h1>
            <p>Ubah detail tugas Anda</p>
        </div>
        
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-section">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $task['id']; ?>">
                    
                    <div class="form-group">
                        <label for="nama_tugas">Nama Tugas:</label>
                        <input type="text" id="nama_tugas" name="nama_tugas" value="<?php echo htmlspecialchars($task['nama_tugas']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prioritas">Prioritas:</label>
                            <select id="prioritas" name="prioritas" required>
                                <option value="rendah" <?php echo $task['prioritas'] == 'rendah' ? 'selected' : ''; ?>>Rendah</option>
                                <option value="sedang" <?php echo $task['prioritas'] == 'sedang' ? 'selected' : ''; ?>>Sedang</option>
                                <option value="tinggi" <?php echo $task['prioritas'] == 'tinggi' ? 'selected' : ''; ?>>Tinggi</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal">Tanggal:</label>
                            <input type="date" id="tanggal" name="tanggal" value="<?php echo $task['tanggal']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="deadline">Deadline:</label>
                        <input type="date" id="deadline" name="deadline" value="<?php echo $task['deadline']; ?>" required>
                    </div>
                    
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary">Update Tugas</button>
                        <a href="index.php" class="btn btn-warning">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>