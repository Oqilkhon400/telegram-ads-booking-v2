<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

try {
    if ($booking_id > 0) {
        // Bitta booking
        $stmt = $conn->prepare("
            SELECT 
                b.*,
                b.package_snapshot_used,
                b.package_snapshot_total,
                c.ad_name as customer_name,
                c.contact_person,
                c.phone as customer_phone,
                cp.total_ads,
                cp.used_ads,
                cp.remaining_ads,
                ts.slot_date,
                ts.slot_time,
                u.username as booked_by_name
            FROM bookings b
            JOIN customers c ON b.customer_id = c.id
            JOIN customer_packages cp ON b.customer_package_id = cp.id
            JOIN time_slots ts ON b.time_slot_id = ts.id
            LEFT JOIN users u ON b.booked_by = u.id
            WHERE b.id = ?
        ");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['package_used'] = (int)$row['package_snapshot_used'];
            $row['package_total'] = (int)$row['package_snapshot_total'];
            $row['package_remaining'] = (int)$row['remaining_ads'];
            $row['slot_time'] = substr($row['slot_time'], 0, 5);
            $bookings[] = $row;
        }
        
        send_success(['bookings' => $bookings], 'Booking topildi');
        
    } elseif ($customer_id > 0) {
        // Mijozning barcha bookinglari - PAKET TARIXI
        $stmt = $conn->prepare("
            SELECT 
                b.id,
                b.ad_description,
                b.status,
                b.package_snapshot_used,
                b.package_snapshot_total,
                ts.slot_date,
                ts.slot_time
            FROM bookings b
            JOIN time_slots ts ON b.time_slot_id = ts.id
            WHERE b.customer_id = ?
            ORDER BY b.package_snapshot_used ASC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $customer_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['package_used'] = (int)$row['package_snapshot_used'];
            $row['package_total'] = (int)$row['package_snapshot_total'];
            $row['slot_time'] = substr($row['slot_time'], 0, 5);
            $bookings[] = $row;
        }
        
        send_success(['bookings' => $bookings], 'Bookinglar topildi');
        
    } else {
        send_error('ID yoki Customer ID kerak');
    }
    
} catch (Exception $e) {
    error_log("Read error: " . $e->getMessage());
    send_error($e->getMessage());
}

$conn->close();
?>