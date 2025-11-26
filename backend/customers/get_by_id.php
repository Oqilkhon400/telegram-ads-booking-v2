<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Tizimga kirish kerak']);
    exit;
}

$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customer_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Customer ID noto\'g\'ri']);
    exit;
}

try {
    // Mijoz ma'lumotlarini olish
    $customer_query = "SELECT * FROM customers WHERE id = $customer_id";
    $customer_result = $conn->query($customer_query);
    
    if (!$customer_result || $customer_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Mijoz topilmadi']);
        exit;
    }
    
    $customer = $customer_result->fetch_assoc();
    
    // Mijozning aktiv paketlari
    $packages_query = "
        SELECT 
            cp.id,
            cp.package_id,
            cp.total_ads,
            cp.used_ads,
            cp.remaining_ads,
            cp.status,
            cp.created_at,
            p.price
        FROM customer_packages cp
        LEFT JOIN packages p ON cp.package_id = p.id
        WHERE cp.customer_id = $customer_id 
        AND cp.status = 'active'
        AND cp.remaining_ads > 0
        ORDER BY cp.created_at DESC
    ";
    
    $packages_result = $conn->query($packages_query);
    $packages = [];
    
    if ($packages_result) {
        while ($row = $packages_result->fetch_assoc()) {
            $packages[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'customer' => $customer,
            'packages' => $packages
        ],
        'message' => 'Mijoz ma\'lumotlari'
    ]);
    
} catch (Exception $e) {
    error_log("Get customer by ID error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>