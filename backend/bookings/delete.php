<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Faqat POST metodi qabul qilinadi', 405);
}

$booking_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($booking_id <= 0) {
    send_error('Booking ID noto\'g\'ri');
}

try {
    // 1. Booking ma'lumotlarini olish
    $check = $conn->query("SELECT b.*, ts.id as time_slot_id FROM bookings b JOIN time_slots ts ON b.time_slot_id = ts.id WHERE b.id = $booking_id");
    
    if ($check->num_rows === 0) {
        send_error('Booking topilmadi');
    }
    
    $booking = $check->fetch_assoc();
    $deleted_snapshot = $booking['package_snapshot_used'];
    $package_id = $booking['customer_package_id'];
    
    // 2. Paket qaytarish (agar cancelled bo'lmasa)
    if ($booking['status'] !== 'cancelled') {
        $conn->query("UPDATE customer_packages SET used_ads = used_ads - 1, remaining_ads = remaining_ads + 1, status = 'active' WHERE id = $package_id");
        error_log("DELETE: Package returned for booking $booking_id");
    }
    
    // 3. Time slot bo'shatish
    $conn->query("UPDATE time_slots SET status = 'available' WHERE id = {$booking['time_slot_id']}");
    
    // 4. Booking o'chirish
    $conn->query("DELETE FROM bookings WHERE id = $booking_id");
    
    // 5. QAYTA RAQAMLASH - O'chirilgandan keyingi barcha bookinglarni
    $renumber_query = "
        UPDATE bookings 
        SET package_snapshot_used = package_snapshot_used - 1 
        WHERE customer_package_id = $package_id 
        AND package_snapshot_used > $deleted_snapshot
    ";
    
    $renumber_result = $conn->query($renumber_query);
    
    if ($renumber_result) {
        $affected = $conn->affected_rows;
        error_log("DELETE: Renumbered $affected bookings after snapshot $deleted_snapshot for package $package_id");
    } else {
        error_log("DELETE ERROR: Failed to renumber bookings: " . $conn->error);
    }
    
    send_success(['id' => $booking_id, 'renumbered' => $affected ?? 0], 'Booking o\'chirildi va raqamlash yangilandi');
    
} catch (Exception $e) {
    error_log("Delete error: " . $e->getMessage());
    send_error($e->getMessage());
}

$conn->close();
?>