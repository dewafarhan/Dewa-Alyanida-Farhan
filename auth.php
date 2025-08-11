<?php
require_once 'config.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk redirect ke login jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?error=Silakan login terlebih dahulu");
        exit();
    }
}

// Fungsi untuk redirect ke dashboard jika sudah login
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

// Fungsi untuk logout
function logout() {
    session_start();
    session_destroy();
    header("Location: login.php?success=Berhasil logout");
    exit();
}

// Fungsi untuk validasi password
function validatePassword($password) {
    if (strlen($password) < 6) {
        return "Password minimal 6 karakter";
    }
    return true;
}

// Fungsi untuk validasi email
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Format email tidak valid";
    }
    return true;
}

// Fungsi untuk validasi username
function validateUsername($username) {
    if (strlen($username) < 3) {
        return "Username minimal 3 karakter";
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return "Username hanya boleh huruf, angka, dan underscore";
    }
    return true;
}

// Fungsi untuk hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk verifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fungsi untuk cek apakah username sudah ada
function isUsernameExists($username) {
    global $pdo;
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

// Fungsi untuk cek apakah email sudah ada
function isEmailExists($email) {
    global $pdo;
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

// Fungsi untuk register user baru
function registerUser($username, $email, $password, $nama_lengkap) {
    global $pdo;
    
    try {
        // Validasi input
        $usernameValidation = validateUsername($username);
        if ($usernameValidation !== true) {
            throw new Exception($usernameValidation);
        }
        
        $emailValidation = validateEmail($email);
        if ($emailValidation !== true) {
            throw new Exception($emailValidation);
        }
        
        $passwordValidation = validatePassword($password);
        if ($passwordValidation !== true) {
            throw new Exception($passwordValidation);
        }
        
        if (empty($nama_lengkap)) {
            throw new Exception("Nama lengkap harus diisi");
        }
        
        // Cek duplikat
        if (isUsernameExists($username)) {
            throw new Exception("Username sudah digunakan");
        }
        
        if (isEmailExists($email)) {
            throw new Exception("Email sudah terdaftar");
        }
        
        // Insert user baru
        $hashedPassword = hashPassword($password);
        $sql = "INSERT INTO users (username, email, password, nama_lengkap) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hashedPassword, $nama_lengkap]);
        
        return true;
        
    } catch (Exception $e) {
        throw $e;
    }
}

// Fungsi untuk login user
function loginUser($username, $password) {
    global $pdo;
    
    try {
        $sql = "SELECT id, username, email, password, nama_lengkap FROM users WHERE username = ? OR email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("Username/Email tidak ditemukan");
        }
        
        if (!verifyPassword($password, $user['password'])) {
            throw new Exception("Password salah");
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        
        return true;
        
    } catch (Exception $e) {
        throw $e;
    }
}

// Fungsi untuk mendapatkan info user yang login
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'nama_lengkap' => $_SESSION['nama_lengkap']
    ];
}
?>