<?php
/**
 * Create Package with Payment
 * Mijozga yangi paket yaratish va to'lov qabul qilish
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login qiling']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST kerak']);
    exit;
}

$customer_id = (int)($_POST['customer_id'] ?? 0);
$total_ads = (int)($_POST['total_ads'] ?? 0);
$payment_method = trim($_POST['payment_method'] ?? 'naqd');
$amount = (float)($_POST['amount'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validatsiya
if ($customer_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Mijoz tanlanmagan']);
    exit;
}

if ($total_ads <= 0) {
    echo json_encode(['success' => false, 'error' => 'Reklama sonini kiriting']);
    exit;
}

// To'lov turi tekshirish
$allowed_methods = ['naqd', 'karta', 'nasiya', 'bepul'];
if (!in_array($payment_method, $allowed_methods)) {
    $payment_method = 'naqd';
}

// Bepul bo'lsa summa 0
if ($payment_method === 'bepul') {
    $amount = 0;
}

try {
    $conn->begin_transaction();
    
    // 1. Avval "Custom" paket borligini tekshirish yoki yaratish
    $check = $conn->query("SELECT id FROM packages WHERE name = 'Custom' LIMIT 1");
    
    if ($check->num_rows > 0) {
        $custom_package = $check->fetch_assoc();
        $package_id = $custom_package['id'];
    } else {
        // Custom paket yaratish
        $conn->query("INSERT INTO packages (name, total_ads, price, is_active) VALUES ('Custom', 1, 0, 1)");
        $package_id = $conn->insert_id;
    }
    
    // 2. Customer package yaratish
    $stmt = $conn->prepare("
        INSERT INTO customer_packages (customer_id, package_id, total_ads, used_ads, remaining_ads, status) 
        VALUES (?, ?, ?, 0, ?, 'active')
    ");
    $stmt->bind_param("iiii", $customer_id, $package_id, $total_ads, $total_ads);
    
    if (!$stmt->execute()) {
        throw new Exception('Paket yaratishda xato: ' . $stmt->error);
    }
    
    $customer_package_id = $conn->insert_id;
    
    // 3. To'lov yozuvi yaratish
    $stmt2 = $conn->prepare("
        INSERT INTO payments (customer_id, customer_package_id, amount, payment_method, notes, payment_date, created_by) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?)
    ");
    $user_id = $_SESSION['user_id'];
    $stmt2->bind_param("iidssi", $customer_id, $customer_package_id, $amount, $payment_method, $notes, $user_id);
    
    if (!$stmt2->execute()) {
        throw new Exception('To\'lov yaratishda xato: ' . $stmt2->error);
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'data' => [
            'customer_package_id' => $customer_package_id,
            'total_ads' => $total_ads,
            'payment_method' => $payment_method,
            'amount' => $amount
        ],
        'message' => 'Paket yaratildi'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Create package with payment error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>