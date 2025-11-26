<?php
/**
 * Get System Stats
 * Tizim statistikasi
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login qiling']);
    exit;
}

try {
    $stats = [];
    
    // Customers
    $result = $conn->query("SELECT COUNT(*) as cnt FROM customers");
    $stats['customers'] = (int)$result->fetch_assoc()['cnt'];
    
    // Customer Packages
    $result = $conn->query("SELECT COUNT(*) as cnt FROM customer_packages");
    $stats['customer_packages'] = (int)$result->fetch_assoc()['cnt'];
    
    // Bookings
    $result = $conn->query("SELECT COUNT(*) as cnt FROM bookings");
    $stats['bookings'] = (int)$result->fetch_assoc()['cnt'];
    
    // Payments
    $result = $conn->query("SELECT COUNT(*) as cnt FROM payments");
    $stats['payments'] = (int)$result->fetch_assoc()['cnt'];
    
    // Time slots (booked)
    $result = $conn->query("SELECT COUNT(*) as cnt FROM time_slots WHERE status = 'booked'");
    $stats['booked_slots'] = (int)$result->fetch_assoc()['cnt'];
    
    // Package templates
    $result = $conn->query("SELECT COUNT(*) as cnt FROM packages");
    $stats['package_templates'] = (int)$result->fetch_assoc()['cnt'];
    
    // Jami
    $stats['total'] = $stats['customers'] + $stats['customer_packages'] + $stats['bookings'] + $stats['payments'];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Xatolik: ' . $e->getMessage()
    ]);
}

$conn->close();
?>