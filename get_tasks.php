<?php
require_once 'config.php';
require_once 'auth.php';

function getAllTasks($filter = 'all', $user_id = null) {
    global $pdo;
    
    // Pastikan user_id ada (user sudah login)
    if (!$user_id) {
        return [];
    }
    
    $sql = "SELECT * FROM tasks WHERE user_id = ?";
    $params = [$user_id];
    
    if ($filter == 'selesai') {
        $sql .= " AND status = 'selesai'";
    } elseif ($filter == 'belum_selesai') {
        $sql .= " AND status = 'belum_selesai'";
    }
    
    $sql .= " ORDER BY 
        CASE prioritas 
            WHEN 'tinggi' THEN 1 
            WHEN 'sedang' THEN 2 
            WHEN 'rendah' THEN 3 
        END,
        deadline ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function formatTanggal($tanggal) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    
    $date = date_create($tanggal);
    $day = date_format($date, 'd');
    $month = $bulan[date_format($date, 'm')];
    $year = date_format($date, 'Y');
    
    return $day . ' ' . $month . ' ' . $year;
}

function isOverdue($deadline, $status) {
    return ($status == 'belum_selesai' && $deadline < date('Y-m-d'));
}

function priorityClass($prioritas) {
    return 'priority-' . $prioritas;
}

function statusClass($status) {
    return 'status-' . $status;
}

function getPriorityText($prioritas) {
    $text = [
        'rendah' => 'Rendah',
        'sedang' => 'Sedang', 
        'tinggi' => 'Tinggi'
    ];
    return $text[$prioritas] ?? $prioritas;
}

function getStatusText($status) {
    $text = [
        'selesai' => 'Selesai',
        'belum_selesai' => 'Belum Selesai'
    ];
    return $text[$status] ?? $status;
}
?>