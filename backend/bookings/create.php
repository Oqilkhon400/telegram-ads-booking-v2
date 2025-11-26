<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Faqat POST metodi qabul qilinadi', 405);
}

$customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
$customer_package_id = isset($_POST['customer_package_id']) ? (int)$_POST['customer_package_id'] : 0;
$slot_date = isset($_POST['slot_date']) ? clean_input($_POST['slot_date']) : '';
$slot_time = isset($_POST['slot_time']) ? clean_input($_POST['slot_time']) : '';
$ad_description = isset($_POST['ad_description']) ? clean_input($_POST['ad_description']) : '';
$notes = isset($_POST['notes']) ? clean_input($_POST['notes']) : null;

// ad_description bo'sh bo'lsa, default qiymat
if (empty($ad_description)) {
    $ad_description = '-'; // yoki 'Izohsiz'
}

if ($customer_id <= 0 || $customer_package_id <= 0 || empty($slot_date) || empty($slot_time)) {
    send_error('Barcha maydonlarni to\'ldiring');
}

try {
    // 1. Paket tekshirish
    $cp_stmt = $conn->prepare("SELECT * FROM customer_packages WHERE id = ? AND customer_id = ? AND status = 'active' AND remaining_ads > 0");
    $cp_stmt->bind_param("ii", $customer_package_id, $customer_id);
    $cp_stmt->execute();
    $package = $cp_stmt->get_result()->fetch_assoc();
    
    if (!$package) {
        send_error('Paket topilmadi yoki qoldig\'i yo\'q');
    }
    
    // 2. Time slot yaratish/tekshirish
    $slot_check = $conn->prepare("SELECT id, status FROM time_slots WHERE slot_date = ? AND slot_time = ?");
    $slot_check->bind_param("ss", $slot_date, $slot_time);
    $slot_check->execute();
    $slot_result = $slot_check->get_result();
    
    if ($slot_result->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO time_slots (slot_date, slot_time, status) VALUES (?, ?, 'available')");
        $stmt->bind_param("ss", $slot_date, $slot_time);
        $stmt->execute();
        $time_slot_id = $conn->insert_id;
    } else {
        $slot = $slot_result->fetch_assoc();
        if ($slot['status'] === 'booked') {
            send_error('Bu vaqt band');
        }
        $time_slot_id = $slot['id'];
    }
    
    // 3. SNAPSHOT
    $snapshot_used = $package['used_ads'] + 1;
    $snapshot_total = $package['total_ads'];
    
    // 4. Booking yaratish (prepared statement)
    $booked_by = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO bookings (customer_id, customer_package_id, time_slot_id, ad_description, booked_by, notes, status, package_snapshot_used, package_snapshot_total) VALUES (?, ?, ?, ?, ?, ?, 'scheduled', ?, ?)");
    $stmt->bind_param("iiisisii", $customer_id, $customer_package_id, $time_slot_id, $ad_description, $booked_by, $notes, $snapshot_used, $snapshot_total);
    
    if ($stmt->execute()) {
        $booking_id = $conn->insert_id;
        
        send_success([
            'id' => $booking_id,
            'customer_id' => $customer_id,
            'slot_date' => $slot_date,
            'slot_time' => $slot_time,
            'package_snapshot_used' => $snapshot_used,
            'package_snapshot_total' => $snapshot_total
        ], 'Booking muvaffaqiyatli');
    } else {
        send_error('Booking yaratishda xato: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Booking error: " . $e->getMessage());
    send_error($e->getMessage());
}

$conn->close();
?>