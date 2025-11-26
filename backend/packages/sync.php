<?php
/**
 * Sync Packages with Bookings
 * Paketlarni bookinglar bilan sinxronlash
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

try {
    // Barcha paketlarni tekshirish
    $sql = "
        SELECT 
            cp.id,
            cp.customer_id,
            cp.total_ads,
            cp.used_ads as recorded_used,
            cp.remaining_ads as recorded_remaining,
            cp.status as current_status,
            c.ad_name as customer_name,
            (SELECT COUNT(*) FROM bookings b WHERE b.customer_package_id = cp.id AND b.status != 'cancelled') as actual_used
        FROM customer_packages cp
        JOIN customers c ON cp.customer_id = c.id
    ";
    
    $result = $conn->query($sql);
    
    $issues = [];
    $fixed = 0;
    
    while ($row = $result->fetch_assoc()) {
        $recorded_used = (int)$row['recorded_used'];
        $actual_used = (int)$row['actual_used'];
        $total_ads = (int)$row['total_ads'];
        
        // Agar mos kelmasa
        if ($recorded_used !== $actual_used) {
            $correct_remaining = $total_ads - $actual_used;
            $correct_status = $correct_remaining <= 0 ? 'completed' : 'active';
            
            // Tuzatish
            $update = $conn->prepare("
                UPDATE customer_packages 
                SET used_ads = ?, remaining_ads = ?, status = ?
                WHERE id = ?
            ");
            $update->bind_param("iisi", $actual_used, $correct_remaining, $correct_status, $row['id']);
            $update->execute();
            
            $issues[] = [
                'package_id' => $row['id'],
                'customer' => $row['customer_name'],
                'total' => $total_ads,
                'was' => [
                    'used' => $recorded_used,
                    'remaining' => (int)$row['recorded_remaining']
                ],
                'now' => [
                    'used' => $actual_used,
                    'remaining' => $correct_remaining
                ]
            ];
            
            $fixed++;
        }
    }
    
    if ($fixed > 0) {
        send_success([
            'fixed_count' => $fixed,
            'issues' => $issues
        ], "$fixed ta paket tuzatildi");
    } else {
        send_success([
            'fixed_count' => 0,
            'issues' => []
        ], 'Barcha paketlar to\'g\'ri');
    }
    
} catch (Exception $e) {
    error_log("Sync error: " . $e->getMessage());
    send_error('Xatolik: ' . $e->getMessage());
}

$conn->close();
?>