<?php
/**
 * Get Package By ID
 * ID orqali bitta paketni olish
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

// Session tekshirish
if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

// Faqat GET metodini qabul qilish
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_error('Faqat GET metodi qabul qilinadi', 405);
}

// ID tekshirish
if (!isset($_GET['id']) || empty($_GET['id'])) {
    send_error('ID majburiy');
}

$id = intval($_GET['id']);

try {
    // Paketni olish
    $stmt = $conn->prepare("
        SELECT 
            cp.id,
            cp.customer_id,
            c.ad_name as customer_name,
            c.phone,
            cp.package_id,
            cp.total_ads,
            cp.used_ads,
            cp.remaining_ads,
            cp.status,
            cp.created_at,
            cp.updated_at,
            p.amount as payment_amount,
            p.payment_method,
            p.payment_date,
            p.notes as payment_notes
        FROM customer_packages cp
        JOIN customers c ON cp.customer_id = c.id
        LEFT JOIN payments p ON cp.id = p.customer_package_id
        WHERE cp.id = ?
        LIMIT 1
    ");
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('Paket topilmadi', 404);
    }
    
    $package = $result->fetch_assoc();
    
    // Bookinglar statistikasi
    $bookings_stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
            SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM bookings
        WHERE customer_package_id = ?
    ");
    
    $bookings_stmt->bind_param("i", $id);
    $bookings_stmt->execute();
    $bookings_stats = $bookings_stmt->get_result()->fetch_assoc();
    
    // Oxirgi bookinglar
    $recent_bookings = $conn->prepare("
        SELECT 
            b.id,
            b.ad_description,
            b.status,
            ts.slot_date,
            ts.slot_time
        FROM bookings b
        JOIN time_slots ts ON b.time_slot_id = ts.id
        WHERE b.customer_package_id = ?
        ORDER BY ts.slot_date DESC, ts.slot_time DESC
        LIMIT 5
    ");
    
    $recent_bookings->bind_param("i", $id);
    $recent_bookings->execute();
    $bookings_result = $recent_bookings->get_result();
    
    $bookings_list = [];
    while ($booking = $bookings_result->fetch_assoc()) {
        $bookings_list[] = [
            'id' => (int)$booking['id'],
            'description' => $booking['ad_description'],
            'status' => $booking['status'],
            'date' => format_date($booking['slot_date']),
            'time' => substr($booking['slot_time'], 0, 5)
        ];
    }
    
    send_success([
        'package' => [
            'id' => (int)$package['id'],
            'customer_id' => (int)$package['customer_id'],
            'customer_name' => $package['customer_name'],
            'phone' => format_phone($package['phone']),
            'package_id' => (int)$package['package_id'],
            'total_ads' => (int)$package['total_ads'],
            'used_ads' => (int)$package['used_ads'],
            'remaining_ads' => (int)$package['remaining_ads'],
            'status' => $package['status'],
            'payment_amount' => format_money($package['payment_amount'] ?? 0),
            'payment_method' => $package['payment_method'] ?? 'bepul',
            'payment_date' => $package['payment_date'] ? format_date($package['payment_date']) : null,
            'payment_notes' => $package['payment_notes'],
            'created_at' => format_datetime($package['created_at']),
            'updated_at' => format_datetime($package['updated_at']),
            'usage_percentage' => round(($package['used_ads'] / $package['total_ads']) * 100, 1)
        ],
        'bookings' => [
            'total' => (int)$bookings_stats['total_bookings'],
            'scheduled' => (int)$bookings_stats['scheduled'],
            'published' => (int)$bookings_stats['published'],
            'cancelled' => (int)$bookings_stats['cancelled'],
            'recent' => $bookings_list
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get package by ID error: " . $e->getMessage());
    send_error('Paket ma\'lumotlarini olishda xatolik yuz berdi');
}

$conn->close();
?>