<?php
require_once 'auth.php';
require_once 'get_tasks.php';

// Cek apakah user sudah login
requireLogin();
$user = getCurrentUser();

// Proses update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $password_lama = $_POST['password_lama'];
        $password_baru = $_POST['password_baru'];
        $konfirmasi_password = $_POST['konfirmasi_password'];
        
        if (empty($nama_lengkap) || empty($email)) {
            throw new Exception("Nama lengkap dan email harus diisi");
        }
        
        $emailValidation = validateEmail($email);
        if ($emailValidation !== true) {
            throw new Exception($emailValidation);
        }
        
        // Cek apakah email sudah digunakan user lain
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $user['id']]);
        if ($stmt->fetch()) {
            throw new Exception("Email sudah digunakan user lain");
        }
        
        // Update nama dan email
        $sql = "UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nama_lengkap, $email, $user['id']]);
        
        // Update password jika diisi
        if (!empty($password_baru)) {
            // Verifikasi password lama
            $sql = "SELECT password FROM users WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user['id']]);
            $user_data = $stmt->fetch();
            
            if (!verifyPassword($password_lama, $user_data['password'])) {
                throw new Exception("Password lama salah");
            }
            
            if ($password_baru !== $konfirmasi_password) {
                throw new Exception("Konfirmasi password baru tidak cocok");
            }
            
            $passwordValidation = validatePassword($password_baru);
            if ($passwordValidation !== true) {
                throw new Exception($passwordValidation);
            }
            
            $hashedPassword = hashPassword($password_baru);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$hashedPassword, $user['id']]);
        }
        
        // Update session
        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['email'] = $email;
        
        $success = "Profile berhasil diupdate!";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get statistics
$all_tasks = getAllTasks('all', $user['id']);
$completed_tasks = getAllTasks('selesai', $user['id']);
$pending_tasks = getAllTasks('belum_selesai', $user['id']);
$total = count($all_tasks);
$completed = count($completed_tasks);
$pending = count($pending_tasks);

// Get overdue tasks
$overdue_count = 0;
foreach ($pending_tasks as $task) {
    if ($task['deadline'] < date('Y-m-d')) {
        $overdue_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👤 Profile User</h1>
            <p>Kelola informasi akun Anda</p>
            <div style="margin-top: 15px;">
                <a href="index.php" class="btn" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 8px 16px; border-radius: 5px; font-size: 14px;">
                    🏠 Kembali ke Dashboard
                </a>
            </div>
        </div>
        
        <div class="content">
            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-row" style="gap: 30px;">
                <!-- Profile Form -->
                <div style="flex: 1;">
                    <div class="form-section">
                        <h2>✏️ Edit Profile</h2>
                        <form method="POST">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: #f8f9fa;">
                                <small style="color: #666; font-size: 0.85rem;">Username tidak dapat diubah</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap:</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" 
                                       value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
                            
                            <h3 style="color: #333; margin-bottom: 15px;">🔒 Ubah Password (Opsional)</h3>
                            
                            <div class="form-group">
                                <label for="password_lama">Password Lama:</label>
                                <input type="password" id="password_lama" name="password_lama">
                            </div>
                            
                            <div class="form-group">
                                <label for="password_baru">Password Baru:</label>
                                <input type="password" id="password_baru" name="password_baru">
                                <small style="color: #666; font-size: 0.85rem;">Minimal 6 karakter</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="konfirmasi_password">Konfirmasi Password Baru:</label>
                                <input type="password" id="konfirmasi_password" name="konfirmasi_password">
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">💾 Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div style="flex: 1;">
                    <div class="form-section">
                        <h2>📊 Statistik Tugas Anda</h2>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                            <div style="text-align: center; padding: 15px; background: #e3f2fd; border-radius: 8px;">
                                <h3 style="color: #1976d2; margin: 0; font-size: 2rem;"><?php echo $total; ?></h3>
                                <p style="margin: 5px 0 0 0; color: #666;">Total Tugas</p>
                            </div>
                            
                            <div style="text-align: center; padding: 15px; background: #e8f5e8; border-radius: 8px;">
                                <h3 style="color: #388e3c; margin: 0; font-size: 2rem;"><?php echo $completed; ?></h3>
                                <p style="margin: 5px 0 0 0; color: #666;">Selesai</p>
                            </div>
                            
                            <div style="text-align: center; padding: 15px; background: #fff3e0; border-radius: 8px;">
                                <h3 style="color: #f57c00; margin: 0; font-size: 2rem;"><?php echo $pending; ?></h3>
                                <p style="margin: 5px 0 0 0; color: #666;">Belum Selesai</p>
                            </div>
                            
                            <div style="text-align: center; padding: 15px; background: #ffebee; border-radius: 8px;">
                                <h3 style="color: #d32f2f; margin: 0; font-size: 2rem;"><?php echo $overdue_count; ?></h3>
                                <p style="margin: 5px 0 0 0; color: #666;">Terlambat</p>
                            </div>
                        </div>
                        
                        <?php if ($total > 0): ?>
                            <div style="text-align: center; padding: 20px; background: #f5f5f5; border-radius: 8px;">
                                <h3 style="color: #4a90e2; margin: 0; font-size: 2.5rem;">
                                    <?php echo round(($completed / $total) * 100, 1); ?>%
                                </h3>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 1.1rem;">Progress Keseluruhan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-section">
                        <h2>ℹ️ Informasi Akun</h2>
                        <div style="line-height: 1.8;">
                            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                            <p><strong>Bergabung:</strong> Pengguna aktif</p>
                        </div>
                    </div>
                </div>
            </div>
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

        // Password confirmation validation
        document.querySelector('input[name="konfirmasi_password"]').addEventListener('input', function() {
            const password = document.querySelector('input[name="password_baru"]').value;
            const confirmPassword = this.value;
            
            if (password && password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
            } else {
                this.setCustomValidity('');
            }
        });

        // Password requirement validation
        document.querySelector('input[name="password_baru"]').addEventListener('input', function() {
            const passwordLama = document.querySelector('input[name="password_lama"]');
            
            if (this.value && !passwordLama.value) {
                passwordLama.setCustomValidity('Password lama harus diisi untuk mengubah password');
            } else {
                passwordLama.setCustomValidity('');
            }
        });
    </script>
</body>
</html>