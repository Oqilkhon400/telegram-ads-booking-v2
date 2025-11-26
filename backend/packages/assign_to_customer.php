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
$package_id = (int)($_POST['package_id'] ?? 0);

error_log("Assign: customer_id=$customer_id, package_id=$package_id");

if ($customer_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'customer_id noto\'g\'ri']);
    exit;
}

if ($package_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'package_id noto\'g\'ri']);
    exit;
}

try {
    // Package ma'lumotlarini olish
    $pkg_result = $conn->query("SELECT * FROM packages WHERE id = $package_id");
    
    if ($pkg_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Paket topilmadi']);
        exit;
    }
    
    $package = $pkg_result->fetch_assoc();
    $total_ads = $package['total_ads'];
    
    // Customer package yaratish
    $sql = "INSERT INTO customer_packages (customer_id, package_id, total_ads, used_ads, remaining_ads, status) 
            VALUES ($customer_id, $package_id, $total_ads, 0, $total_ads, 'active')";
    
    error_log("SQL: $sql");
    
    $conn->query($sql);
    $customer_package_id = $conn->insert_id;
    
    error_log("Created customer_package_id: $customer_package_id");
    
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $customer_package_id,
            'customer_id' => $customer_id,
            'package_id' => $package_id
        ],
        'message' => 'Paket biriktirildi'
    ]);
    
} catch (Exception $e) {
    error_log("Assign error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>