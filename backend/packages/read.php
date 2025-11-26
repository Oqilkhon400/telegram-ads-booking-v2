<?php
/**
 * Read Packages
 * Paketlar ro'yxatini olish
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

try {
    // Parametrlar
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $items_per_page = ITEMS_PER_PAGE;
    $offset = ($page - 1) * $items_per_page;
    
    $status = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';
    $customer_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
    
    // Base query
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    // Status filter
    if ($status !== 'all') {
        $where_conditions[] = "cp.status = ?";
        $params[] = $status;
        $param_types .= 's';
    }
    
    // Customer filter
    if ($customer_id > 0) {
        $where_conditions[] = "cp.customer_id = ?";
        $params[] = $customer_id;
        $param_types .= 'i';
    }
    
    $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM customer_packages cp
        $where_sql
    ";
    
    if (!empty($params)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($param_types, ...$params);
        $count_stmt->execute();
        $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total_items = $conn->query($count_sql)->fetch_assoc()['total'];
    }
    
    // Get packages with customer and payment info
    $sql = "
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
            p.amount as payment_amount,
            p.payment_method,
            p.payment_date
        FROM customer_packages cp
        JOIN customers c ON cp.customer_id = c.id
        LEFT JOIN payments p ON cp.id = p.customer_package_id
        $where_sql
        ORDER BY cp.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $items_per_page;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $packages = [];
    while ($row = $result->fetch_assoc()) {
        $packages[] = [
            'id' => (int)$row['id'],
            'customer_id' => (int)$row['customer_id'],
            'customer_name' => $row['customer_name'],
            'phone' => format_phone($row['phone']),
            'package_id' => (int)$row['package_id'],
            'total_ads' => (int)$row['total_ads'],
            'used_ads' => (int)$row['used_ads'],
            'remaining_ads' => (int)$row['remaining_ads'],
            'status' => $row['status'],
            'payment_amount' => format_money($row['payment_amount'] ?? 0),
            'payment_method' => $row['payment_method'] ?? 'bepul',
            'payment_date' => $row['payment_date'] ? format_date($row['payment_date']) : null,
            'created_at' => format_datetime($row['created_at']),
            'usage_percentage' => round(($row['used_ads'] / $row['total_ads']) * 100, 1)
        ];
    }
    
    // Pagination
    $total_pages = ceil($total_items / $items_per_page);
    
    send_success([
        'packages' => $packages,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => (int)$total_items,
            'items_per_page' => $items_per_page,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Read packages error: " . $e->getMessage());
    send_error('Paketlarni olishda xatolik yuz berdi');
}

$conn->close();
?>