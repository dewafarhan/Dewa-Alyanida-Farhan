<?php
require_once 'get_tasks.php';
require_once 'auth.php';

// Cek apakah user sudah login
requireLogin();
$user = getCurrentUser();

// Ambil filter dari URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$tasks = getAllTasks($filter, $user['id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List - Manajemen Tugas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 To-Do List</h1>
            <p>Selamat datang, <strong><?php echo htmlspecialchars($user['nama_lengkap']); ?></strong>!</p>
            <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <a href="logout.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 8px 16px; border-radius: 5px; font-size: 14px;" 
                   onclick="return confirm('Apakah Anda yakin ingin logout?')">
                    🚪 Logout
                </a>
            </div>
        </div>
        
        <div class="content">
            <!-- Alert Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Form Tambah Tugas -->
            <div class="form-section">
                <h2>➕ Tambah Tugas Baru</h2>
                <form method="POST" action="add_task.php">
                    <div class="form-group">
                        <label for="nama_tugas">Nama Tugas:</label>
                        <input type="text" id="nama_tugas" name="nama_tugas" placeholder="Masukkan nama tugas..." required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="prioritas">Prioritas:</label>
                            <select id="prioritas" name="prioritas" required>
                                <option value="sedang">Sedang</option>
                                <option value="rendah">Rendah</option>
                                <option value="tinggi">Tinggi</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tanggal">Tanggal:</label>
                            <input type="date" id="tanggal" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="deadline">Deadline:</label>
                        <input type="date" id="deadline" name="deadline" required>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Tambah Tugas</button>
                    </div>
                </form>
            </div>
            
            <!-- Filter Tugas -->
            <div class="mb-3">
                <a href="index.php?filter=all" class="btn <?php echo $filter == 'all' ? 'btn-primary' : 'btn-warning'; ?>">Semua Tugas</a>
                <a href="index.php?filter=belum_selesai" class="btn <?php echo $filter == 'belum_selesai' ? 'btn-primary' : 'btn-warning'; ?>">Belum Selesai</a>
                <a href="index.php?filter=selesai" class="btn <?php echo $filter == 'selesai' ? 'btn-primary' : 'btn-warning'; ?>">Selesai</a>
            </div>
            
            <!-- Daftar Tugas -->
            <div class="task-section">
                <h2>📋 Daftar Tugas 
                    <?php 
                    if ($filter == 'selesai') echo '(Selesai)';
                    elseif ($filter == 'belum_selesai') echo '(Belum Selesai)';
                    ?>
                </h2>
                
                <?php if (empty($tasks)): ?>
                    <div class="text-center">
                        <p style="color: #666; font-size: 1.1rem; margin: 40px 0;">
                            <?php 
                            if ($filter == 'selesai') echo 'Belum ada tugas yang selesai.';
                            elseif ($filter == 'belum_selesai') echo 'Semua tugas sudah selesai! 🎉';
                            else echo 'Belum ada tugas. Tambahkan tugas pertama Anda!';
                            ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="task-list">
                        <?php foreach ($tasks as $task): ?>
                            <div class="task-item <?php echo $task['status'] == 'selesai' ? 'completed' : ''; ?>">
                                <div class="task-header">
                                    <h3 class="task-title"><?php echo htmlspecialchars($task['nama_tugas']); ?></h3>
                                </div>
                                
                                <div class="task-meta">
                                    <div class="meta-item">
                                        <strong>Status:</strong> 
                                        <span class="status-badge <?php echo statusClass($task['status']); ?>">
                                            <?php echo getStatusText($task['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="meta-item">
                                        <strong>Prioritas:</strong> 
                                        <span class="priority-badge <?php echo priorityClass($task['prioritas']); ?>">
                                            <?php echo getPriorityText($task['prioritas']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="meta-item">
                                        <strong>Tanggal:</strong> <?php echo formatTanggal($task['tanggal']); ?>
                                    </div>
                                    
                                    <div class="meta-item">
                                        <strong>Deadline:</strong> 
                                        <?php echo formatTanggal($task['deadline']); ?>
                                        <?php if (isOverdue($task['deadline'], $task['status'])): ?>
                                            <span style="color: #dc3545; font-weight: bold;"> (Terlambat!)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="task-actions">
                                    <?php if ($task['status'] == 'belum_selesai'): ?>
                                        <a href="toggle_status.php?id=<?php echo $task['id']; ?>" class="btn btn-success btn-sm">
                                            ✓ Tandai Selesai
                                        </a>
                                    <?php else: ?>
                                        <a href="toggle_status.php?id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm">
                                            ↶ Tandai Belum Selesai
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-warning btn-sm">
                                        ✏️ Edit
                                    </a>
                                    
                                    <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus tugas \'<?php echo htmlspecialchars($task['nama_tugas']); ?>\'?')">
                                        🗑️ Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Statistics -->
            <?php 
            $all_tasks = getAllTasks('all', $user['id']);
            $completed_tasks = getAllTasks('selesai', $user['id']);
            $pending_tasks = getAllTasks('belum_selesai', $user['id']);
            $total = count($all_tasks);
            $completed = count($completed_tasks);
            $pending = count($pending_tasks);
            ?>
            
            <?php if ($total > 0): ?>
                <div class="form-section">
                    <h2>📊 Statistik Tugas</h2>
                    <div class="form-row">
                        <div class="text-center">
                            <h3 style="color: #4a90e2;"><?php echo $total; ?></h3>
                            <p>Total Tugas</p>
                        </div>
                        <div class="text-center">
                            <h3 style="color: #28a745;"><?php echo $completed; ?></h3>
                            <p>Selesai</p>
                        </div>
                        <div class="text-center">
                            <h3 style="color: #ffc107;"><?php echo $pending; ?></h3>
                            <p>Belum Selesai</p>
                        </div>
                        <!-- <div class="text-center">
                            <h3 style="color: #17a2b8;"><?php echo $total > 0 ? round(($completed / $total) * 100, 1) : 0; ?>%</h3>
                            <p>Progress</p>
                        </div> -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);

        // Set minimum date for deadline
        document.getElementById('tanggal').addEventListener('change', function() {
            document.getElementById('deadline').min = this.value;
        });

        // Set initial minimum deadline to today's date
        document.getElementById('deadline').min = document.getElementById('tanggal').value;
    </script>
</body>
</html>