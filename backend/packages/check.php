<?php
/**
 * Check Package Sync Status
 * Paketlar holatini tekshirish
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

try {
    $sql = "
        SELECT 
            cp.id,
            cp.customer_id,
            cp.total_ads,
            cp.used_ads as recorded_used,
            cp.remaining_ads as recorded_remaining,
            cp.status,
            c.ad_name as customer_name,
            c.phone,
            (SELECT COUNT(*) FROM bookings b WHERE b.customer_package_id = cp.id AND b.status != 'cancelled') as actual_used
        FROM customer_packages cp
        JOIN customers c ON cp.customer_id = c.id
        ORDER BY c.ad_name
    ";
    
    $result = $conn->query($sql);
    
    $all_packages = [];
    $issues = [];
    
    while ($row = $result->fetch_assoc()) {
        $recorded_used = (int)$row['recorded_used'];
        $actual_used = (int)$row['actual_used'];
        $is_synced = ($recorded_used === $actual_used);
        
        $package = [
            'id' => (int)$row['id'],
            'customer_name' => $row['customer_name'],
            'phone' => $row['phone'],
            'total_ads' => (int)$row['total_ads'],
            'recorded_used' => $recorded_used,
            'actual_used' => $actual_used,
            'recorded_remaining' => (int)$row['recorded_remaining'],
            'actual_remaining' => (int)$row['total_ads'] - $actual_used,
            'status' => $row['status'],
            'is_synced' => $is_synced
        ];
        
        $all_packages[] = $package;
        
        if (!$is_synced) {
            $issues[] = $package;
        }
    }
    
    send_success([
        'total_packages' => count($all_packages),
        'synced_packages' => count($all_packages) - count($issues),
        'issues_count' => count($issues),
        'issues' => $issues,
        'all_packages' => $all_packages
    ]);
    
} catch (Exception $e) {
    error_log("Check error: " . $e->getMessage());
    send_error('Xatolik: ' . $e->getMessage());
}

$conn->close();
?>