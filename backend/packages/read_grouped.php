<?php
/**
 * Read Packages Grouped by Customer
 * Mijozlar bo'yicha guruhlangan paketlar
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

$status = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';

try {
    // Status filter
    $status_condition = "";
    if ($status === 'active') {
        $status_condition = "AND cp.status = 'active'";
    } elseif ($status === 'completed') {
        $status_condition = "AND cp.status = 'completed'";
    }
    
    // Search filter
    $search_condition = "";
    if ($search) {
        $search_condition = "AND (c.ad_name LIKE '%$search%' OR c.phone LIKE '%$search%' OR c.contact_person LIKE '%$search%')";
    }
    
    // Mijozlar va ularning paketlarini olish
    $sql = "
        SELECT 
            c.id as customer_id,
            c.ad_name as customer_name,
            c.phone,
            cp.id as package_id,
            cp.total_ads,
            cp.used_ads,
            cp.remaining_ads,
            cp.status as package_status,
            cp.created_at,
            COALESCE(p.amount, 0) as payment_amount,
            COALESCE(p.payment_method, 'bepul') as payment_method
        FROM customers c
        INNER JOIN customer_packages cp ON c.id = cp.customer_id
        LEFT JOIN payments p ON cp.id = p.customer_package_id
        WHERE 1=1 $status_condition $search_condition
        ORDER BY c.ad_name ASC, cp.created_at DESC
    ";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('SQL xato: ' . $conn->error);
    }
    
    // Mijozlar bo'yicha guruhlab chiqish
    $grouped = [];
    
    while ($row = $result->fetch_assoc()) {
        $customer_id = $row['customer_id'];
        
        if (!isset($grouped[$customer_id])) {
            $grouped[$customer_id] = [
                'customer_id' => (int)$customer_id,
                'customer_name' => $row['customer_name'],
                'phone' => format_phone($row['phone']),
                'packages' => [],
                'active_packages' => 0,
                'completed_packages' => 0
            ];
        }
        
        // Paket ma'lumotlari
        $package = [
            'id' => (int)$row['package_id'],
            'total_ads' => (int)$row['total_ads'],
            'used_ads' => (int)$row['used_ads'],
            'remaining_ads' => (int)$row['remaining_ads'],
            'status' => $row['package_status'],
            'payment_amount' => format_money($row['payment_amount']),
            'payment_method' => $row['payment_method'],
            'created_at' => format_datetime($row['created_at']),
            'customer_name' => $row['customer_name']
        ];
        
        $grouped[$customer_id]['packages'][] = $package;
        
        // Statistika
        if ($row['package_status'] === 'active') {
            $grouped[$customer_id]['active_packages']++;
        } else {
            $grouped[$customer_id]['completed_packages']++;
        }
    }
    
    // Array formatga o'tkazish
    $grouped_packages = array_values($grouped);
    
    // Shuningdek, oddiy paketlar ro'yxatini ham qaytarish (calendar uchun)
    $packages_sql = "SELECT id, name, total_ads, price, is_active FROM packages WHERE is_active = 1 ORDER BY total_ads ASC";
    $packages_result = $conn->query($packages_sql);
    
    $packages = [];
    if ($packages_result) {
        while ($row = $packages_result->fetch_assoc()) {
            $packages[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'total_ads' => (int)$row['total_ads'],
                'price' => format_money($row['price'])
            ];
        }
    }
    
    send_success([
        'grouped_packages' => $grouped_packages,
        'packages' => $packages,
        'total_customers' => count($grouped_packages)
    ]);
    
} catch (Exception $e) {
    error_log("Read grouped packages error: " . $e->getMessage());
    send_error('Xatolik: ' . $e->getMessage());
}

$conn->close();
?>