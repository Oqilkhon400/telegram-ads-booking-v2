<?php
/**
 * Read Payments
 * To'lovlar ro'yxatini olish
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
    $payment_method = isset($_GET['payment_method']) ? clean_input($_GET['payment_method']) : 'all';
    $date_from = isset($_GET['date_from']) ? clean_input($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? clean_input($_GET['date_to']) : '';
    
    // Base query
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    // Search
    if (!empty($search)) {
        $where_conditions[] = "(c.ad_name LIKE ? OR c.phone LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $param_types .= 'ss';
    }
    
    // Payment method filter
    if ($payment_method !== 'all') {
        $where_conditions[] = "p.payment_method = ?";
        $params[] = $payment_method;
        $param_types .= 's';
    }
    
    // Date from
    if (!empty($date_from)) {
        $where_conditions[] = "p.payment_date >= ?";
        $params[] = $date_from;
        $param_types .= 's';
    }
    
    // Date to
    if (!empty($date_to)) {
        $where_conditions[] = "p.payment_date <= ?";
        $params[] = $date_to;
        $param_types .= 's';
    }
    
    $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Count total
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM payments p
        JOIN customers c ON p.customer_id = c.id
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
    
    // Get payments
    $sql = "
        SELECT 
            p.id,
            p.customer_id,
            c.ad_name as customer_name,
            c.phone as customer_phone,
            p.amount,
            p.payment_date,
            p.payment_method,
            p.notes,
            p.created_at
        FROM payments p
        JOIN customers c ON p.customer_id = c.id
        $where_sql
        ORDER BY p.payment_date DESC, p.created_at DESC
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
    
    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = [
            'id' => (int)$row['id'],
            'customer_id' => (int)$row['customer_id'],
            'customer_name' => $row['customer_name'],
            'customer_phone' => format_phone($row['customer_phone']),
            'amount' => number_format($row['amount'], 0, ',', ' '),
            'payment_date' => format_date($row['payment_date']),
            'payment_method' => $row['payment_method'],
            'notes' => $row['notes'],
            'created_at' => format_datetime($row['created_at'])
        ];
    }
    
    // Pagination
    $total_pages = ceil($total_items / $items_per_page);
    
    send_success([
        'payments' => $payments,
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
    error_log("Read payments error: " . $e->getMessage());
    send_error('To\'lovlarni olishda xatolik yuz berdi');
}

$conn->close();
?>