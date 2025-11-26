<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

$date = isset($_GET['date']) ? clean_input($_GET['date']) : date('Y-m-d');

try {
    // Predefined time slots
    $time_slots = [
        '07:00', '08:00', '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00', '17:00', '18:00',
        '19:00', '20:00', '21:00', '22:00'
    ];
    
    $slots_data = [];
    $stats = [
        'available' => 0,
        'booked' => 0,
        'past' => 0
    ];
    
    // Har bir vaqt uchun booking borligini tekshirish
    foreach ($time_slots as $time) {
        $stmt = $conn->prepare("
            SELECT 
                ts.id as slot_id,
                ts.status as slot_status,
                b.id as booking_id,
                b.ad_description,
                b.package_snapshot_used,
                b.package_snapshot_total,
                c.ad_name as customer_name,
                c.phone as customer_phone,
                cp.remaining_ads as package_remaining
            FROM time_slots ts
            LEFT JOIN bookings b ON ts.id = b.time_slot_id AND b.status != 'cancelled'
            LEFT JOIN customers c ON b.customer_id = c.id
            LEFT JOIN customer_packages cp ON b.customer_package_id = cp.id
            WHERE ts.slot_date = ? AND ts.slot_time = ?
        ");
        
        $stmt->bind_param("ss", $date, $time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $slot = [
                'time' => $time,
                'is_booked' => ($row['booking_id'] !== null),
                'is_past' => false // O'tgan vaqt funksiyasini o'chirish
            ];
            
if ($row['booking_id']) {
                $slot['booking'] = [
                    'id' => (int)$row['booking_id'],
                    'customer_name' => $row['customer_name'],
                    'customer_phone' => format_phone($row['customer_phone']),
                    'ad_description' => $row['ad_description'],
                    'package_used' => (int)$row['package_snapshot_used'], // SNAPSHOT
                    'package_total' => (int)$row['package_snapshot_total'], // SNAPSHOT
                    'package_remaining' => (int)$row['package_remaining']
                ];
                $stats['booked']++;
            } else {
                $stats['available']++;
            }
        } else {
            $slot = [
                'time' => $time,
                'is_booked' => false,
                'is_past' => false
            ];
            $stats['available']++;
        }
        
        $slots_data[] = $slot;
    }
    
    send_success([
        'time_slots' => $slots_data,
        'statistics' => $stats
    ], 'Kalendar yuklandi');
    
} catch (Exception $e) {
    error_log("Calendar error: " . $e->getMessage());
    send_error($e->getMessage());
}

$conn->close();
?>