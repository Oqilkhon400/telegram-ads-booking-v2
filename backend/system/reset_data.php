<?php
/**
 * Reset Test Data
 * Test ma'lumotlarini tozalash (XAVFLI!)
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

// Faqat superadmin
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login qiling']);
    exit;
}

// Superadmin tekshirish
$user_check = $conn->query("SELECT role, password FROM users WHERE id = " . $_SESSION['user_id']);
$user = $user_check->fetch_assoc();

if ($user['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'error' => 'Faqat superadmin uchun']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST kerak']);
    exit;
}

$action = $_POST['action'] ?? '';
$confirm_password = $_POST['password'] ?? '';

// Parolni tekshirish
if (!password_verify($confirm_password, $user['password'])) {
    echo json_encode(['success' => false, 'error' => 'Parol noto\'g\'ri!']);
    exit;
}

try {
    $conn->begin_transaction();
    
    $deleted = [
        'bookings' => 0,
        'customer_packages' => 0,
        'payments' => 0,
        'customers' => 0,
        'time_slots' => 0,
        'package_templates' => 0
    ];
    
    if ($action === 'all' || $action === 'bookings') {
        // Bookinglarni o'chirish
        $result = $conn->query("SELECT COUNT(*) as cnt FROM bookings");
        $deleted['bookings'] = $result->fetch_assoc()['cnt'];
        $conn->query("DELETE FROM bookings");
        
        // Time slotlarni tozalash
        $result = $conn->query("SELECT COUNT(*) as cnt FROM time_slots WHERE status = 'booked'");
        $deleted['time_slots'] = $result->fetch_assoc()['cnt'];
        $conn->query("UPDATE time_slots SET status = 'available', booking_id = NULL WHERE status = 'booked'");
    }
    
    if ($action === 'all' || $action === 'packages') {
        // Customer packages
        $result = $conn->query("SELECT COUNT(*) as cnt FROM customer_packages");
        $deleted['customer_packages'] = $result->fetch_assoc()['cnt'];
        $conn->query("DELETE FROM customer_packages");
        
        // Payments
        $result = $conn->query("SELECT COUNT(*) as cnt FROM payments");
        $deleted['payments'] = $result->fetch_assoc()['cnt'];
        $conn->query("DELETE FROM payments");
    }
    
    if ($action === 'all' || $action === 'customers') {
        // Customers
        $result = $conn->query("SELECT COUNT(*) as cnt FROM customers");
        $deleted['customers'] = $result->fetch_assoc()['cnt'];
        $conn->query("DELETE FROM customers");
    }
    
    if ($action === 'all' || $action === 'templates') {
        // Shablon paketlarni o'chirish (packages jadvali)
        $result = $conn->query("SELECT COUNT(*) as cnt FROM packages");
        if ($result) {
            $deleted['package_templates'] = $result->fetch_assoc()['cnt'];
        }
        
        // O'chirish
        $del_result = $conn->query("DELETE FROM packages");
        if (!$del_result) {
            throw new Exception('Packages o\'chirishda xato: ' . $conn->error);
        }
        
        // AUTO_INCREMENT reset
        $conn->query("ALTER TABLE packages AUTO_INCREMENT = 1");
    }
    
    // AUTO_INCREMENT ni reset qilish
    if ($action === 'all') {
        $conn->query("ALTER TABLE bookings AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE customer_packages AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE payments AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE customers AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE packages AUTO_INCREMENT = 1");
    }
    
    $conn->commit();
    
    $total = array_sum($deleted);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'deleted' => $deleted,
            'total' => $total
        ],
        'message' => "Jami $total ta yozuv o'chirildi"
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Reset data error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Xatolik: ' . $e->getMessage()
    ]);
}

$conn->close();
?>