<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../config/settings.php';

if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$status = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 0;
$items_per_page = $limit > 0 ? $limit : 20;
$offset = ($page - 1) * $items_per_page;

try {
    // Count query
    $count_query = "SELECT COUNT(*) as total FROM customers WHERE 1=1";
    
    if ($search) {
        $search_term = "%$search%";
        $count_query .= " AND (ad_name LIKE '$search_term' OR contact_person LIKE '$search_term' OR phone LIKE '$search_term')";
    }
    
    if ($status !== 'all') {
        $is_active = ($status === 'active') ? 1 : 0;
        $count_query .= " AND is_active = $is_active";
    }
    
    $count_result = $conn->query($count_query);
    $total_items = $count_result->fetch_assoc()['total'];
    
    // Main query
    $query = "
        SELECT 
            c.*,
            COUNT(DISTINCT cp.id) as total_packages,
            COALESCE(SUM(cp.remaining_ads), 0) as total_remaining_ads
        FROM customers c
        LEFT JOIN customer_packages cp ON c.id = cp.customer_id AND cp.status = 'active'
        WHERE 1=1
    ";
    
    if ($search) {
        $search_term = "%$search%";
        $query .= " AND (c.ad_name LIKE '$search_term' OR c.contact_person LIKE '$search_term' OR c.phone LIKE '$search_term')";
    }
    
    if ($status !== 'all') {
        $is_active = ($status === 'active') ? 1 : 0;
        $query .= " AND c.is_active = $is_active";
    }
    
    $query .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT $items_per_page OFFSET $offset";
    
    $result = $conn->query($query);
    $customers = [];
    
    while ($row = $result->fetch_assoc()) {
        $row['created_at'] = format_datetime($row['created_at']);
        $row['phone'] = format_phone($row['phone']);
        $row['is_active'] = (bool)$row['is_active'];
        $row['total_packages'] = (int)$row['total_packages'];
        $row['total_remaining_ads'] = (int)$row['total_remaining_ads'];
        $customers[] = $row;
    }
    
    $total_pages = ceil($total_items / $items_per_page);
    
    send_success([
        'customers' => $customers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_items' => (int)$total_items,
            'items_per_page' => $items_per_page,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ], 'Mijozlar yuklandi');
    
} catch (Exception $e) {
    error_log("Read customers error: " . $e->getMessage());
    send_error($e->getMessage());
}

$conn->close();
?>