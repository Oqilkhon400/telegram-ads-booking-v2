<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login qiling']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST kerak']);
    exit;
}

$customer_id = (int)($_POST['customer_id'] ?? 0);
$customer_package_id = (int)($_POST['customer_package_id'] ?? 0);
$amount = (float)($_POST['amount'] ?? 0);
$payment_method = $_POST['payment_method'] ?? '';
$notes = $_POST['notes'] ?? '';
$payment_date = date('Y-m-d');
$created_by = $_SESSION['user_id'];

error_log("Payment: customer_id=$customer_id, amount=$amount, method=$payment_method");

if ($customer_id <= 0 || $customer_package_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID\'lar noto\'g\'ri']);
    exit;
}

if ($amount < 0) {
    echo json_encode(['success' => false, 'error' => 'Summa noto\'g\'ri']);
    exit;
}

try {
    $sql = "INSERT INTO payments (customer_id, customer_package_id, amount, payment_date, payment_method, notes, created_by) 
            VALUES ($customer_id, $customer_package_id, $amount, '$payment_date', '$payment_method', '$notes', $created_by)";
    
    error_log("Payment SQL: $sql");
    
    $conn->query($sql);
    $payment_id = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $payment_id
        ],
        'message' => 'To\'lov qabul qilindi'
    ]);
    
} catch (Exception $e) {
    error_log("Payment error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>