<?php
/**
 * Read Nasiya Payments
 * Nasiya to'lovlar ro'yxati
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
    
    $search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
    
    // Base query - using view
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    // Search
    if (!empty($search)) {
        $where_conditions[] = "(customer_name LIKE ? OR phone LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'ss';
    }
    
    $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total
    $count_sql = "SELECT COUNT(*) as total FROM v_nasiya_payments $where_sql";
    
    if (!empty($params)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($param_types, ...$params);
        $count_stmt->execute();
        $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
    } else {
        $total_items = $conn->query($count_sql)->fetch_assoc()['total'];
    }
    
    // Get nasiya payments
    $sql = "
        SELECT 
            id,
            customer_id,
            customer_name,
            contact_person,
            phone,
            customer_package_id,
            total_ads,
            used_ads,
            remaining_ads,
            package_status,
            amount,
            payment_date,
            notes,
            created_at
        FROM v_nasiya_payments
        $where_sql
        ORDER BY payment_date DESC
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
    
    $nasiya_payments = [];
    $total_debt = 0;
    
    while ($row = $result->fetch_assoc()) {
        $nasiya_payments[] = [
            'id' => (int)$row['id'],
            'customer_id' => (int)$row['customer_id'],
            'customer_name' => $row['customer_name'],
            'contact_person' => $row['contact_person'],
            'phone' => format_phone($row['phone']),
            'customer_package_id' => (int)$row['customer_package_id'],
            'package_info' => [
                'total_ads' => (int)$row['total_ads'],
                'used_ads' => (int)$row['used_ads'],
                'remaining_ads' => (int)$row['remaining_ads'],
                'status' => $row['package_status']
            ],
            'amount' => format_money($row['amount']),
            'amount_raw' => (float)$row['amount'],
            'payment_date' => format_date($row['payment_date']),
            'notes' => $row['notes'],
            'created_at' => format_datetime($row['created_at'])
        ];
        
        $total_debt += (float)$row['amount'];
    }
    
    // Pagination
    $total_pages = ceil($total_items / $items_per_page);
    
    send_success([
        'nasiya_payments' => $nasiya_payments,
        'statistics' => [
            'total_debt' => format_money($total_debt),
            'total_debt_raw' => $total_debt,
            'total_count' => (int)$total_items
        ],
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
    error_log("Read nasiya payments error: " . $e->getMessage());
    send_error('Nasiya to\'lovlarni olishda xatolik yuz berdi');
}

$conn->close();
?>