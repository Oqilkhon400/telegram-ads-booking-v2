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
$new_slot_date = isset($_POST['slot_date']) ? clean_input($_POST['slot_date']) : null;
$new_slot_time = isset($_POST['slot_time']) ? clean_input($_POST['slot_time']) : null;
$status = isset($_POST['status']) ? clean_input($_POST['status']) : null;

if ($booking_id <= 0) {
    send_error('Booking ID noto\'g\'ri');
}

try {
    // Booking ma'lumotlarini olish
    $check = $conn->query("SELECT b.*, ts.id as time_slot_id FROM bookings b JOIN time_slots ts ON b.time_slot_id = ts.id WHERE b.id = $booking_id");
    
    if ($check->num_rows === 0) {
        send_error('Booking topilmadi');
    }
    
    $booking = $check->fetch_assoc();
    $old_status = $booking['status']; // Eski statusni saqlash
    
    // Vaqt ko'chirish
    if ($new_slot_date && $new_slot_time) {
        $slot_check = $conn->query("SELECT id, status FROM time_slots WHERE slot_date = '$new_slot_date' AND slot_time = '$new_slot_time'");
        
        if ($slot_check->num_rows === 0) {
            $conn->query("INSERT INTO time_slots (slot_date, slot_time, status) VALUES ('$new_slot_date', '$new_slot_time', 'available')");
            $new_time_slot_id = $conn->insert_id;
        } else {
            $slot = $slot_check->fetch_assoc();
            if ($slot['status'] === 'booked' && $slot['id'] != $booking['time_slot_id']) {
                send_error('Yangi vaqt band');
            }
            $new_time_slot_id = $slot['id'];
        }
        
        $conn->query("UPDATE time_slots SET status = 'available' WHERE id = {$booking['time_slot_id']}");
        $conn->query("UPDATE time_slots SET status = 'booked' WHERE id = $new_time_slot_id");
        $conn->query("UPDATE bookings SET time_slot_id = $new_time_slot_id WHERE id = $booking_id");
        
        error_log("MOVE: Booking $booking_id moved from {$booking['time_slot_id']} to $new_time_slot_id");
    }
    
    // Status o'zgartirish
    if ($status) {
        // TRIGGER avtomatik ravishda:
        // - Agar 'cancelled' bo'lsa - paketga reklama qaytaradi
        // - time_slot ni 'available' qiladi
        $conn->query("UPDATE bookings SET status = '$status' WHERE id = $booking_id");
        
        error_log("STATUS CHANGE: Booking $booking_id - Old: $old_status, New: $status (trigger handles package)");
    }
    
    send_success(['id' => $booking_id], 'Yangilandi');
    
} catch (Exception $e) {
    error_log("Update error: " . $e->getMessage());
    send_error($e->getMessage());
}

$conn->close();
?>