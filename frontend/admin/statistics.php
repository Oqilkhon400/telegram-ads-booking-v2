<?php
/**
 * Statistika Sahifasi
 * Batafsil hisobotlar va grafiklar - ZAMONAVIY DIZAYN
 */

require_once '../../config/database.php';

// Session va autentifikatsiya
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../login.php');
    exit;
}

// Logging function for statistics
function log_statistics_issue($message, $data = []) {
    $log_message = date('Y-m-d H:i:s') . " - STATISTICS: $message";
    if (!empty($data)) {
        $log_message .= " | Data: " . json_encode($data);
    }
    error_log($log_message);
}

// Revenue mismatch tolerance threshold
define('REVENUE_MISMATCH_TOLERANCE', 0.01);

// Sana filtrlari with validation
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Validate dates
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to)) {
    log_statistics_issue('Invalid date format', ['date_from' => $date_from, 'date_to' => $date_to]);
    $date_from = date('Y-m-01');
    $date_to = date('Y-m-d');
}

// Umumiy statistika with parameterized query
// Prioritize payment_date, fallback to created_at when payment_date is NULL
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM customers WHERE is_active = 1) as active_customers,
        (SELECT COUNT(*) FROM customers) as total_customers,
        (SELECT COUNT(*) FROM customer_packages WHERE status = 'active') as active_packages,
        (SELECT COUNT(*) FROM bookings WHERE status = 'scheduled') as scheduled_ads,
        (SELECT COUNT(*) FROM bookings WHERE status = 'published') as published_ads,
        (SELECT SUM(amount) FROM payments 
         WHERE (payment_date BETWEEN ? AND ?) 
            OR (payment_date IS NULL AND DATE(created_at) BETWEEN ? AND ?)) as total_revenue,
        (SELECT COUNT(*) FROM payments 
         WHERE (payment_date BETWEEN ? AND ?) 
            OR (payment_date IS NULL AND DATE(created_at) BETWEEN ? AND ?)) as total_payments
";

$stmt = $conn->prepare($stats_query);
if (!$stmt) {
    log_statistics_issue('Failed to prepare stats query', ['error' => $conn->error]);
    die('Database error occurred');
}

$stmt->bind_param('ssssssss', $date_from, $date_to, $date_from, $date_to, $date_from, $date_to, $date_from, $date_to);
$stmt->execute();
$stats_result = $stmt->get_result();
$stats = $stats_result->fetch_assoc();
$stmt->close();

// Log if no revenue found in the period
if ($stats['total_revenue'] === null || $stats['total_revenue'] == 0) {
    log_statistics_issue('No revenue found for period', ['date_from' => $date_from, 'date_to' => $date_to]);
}

// Oylik daromad (oxirgi 6 oy) - OPTIMIZED: Single query instead of 6 separate queries
$month_name_map = [
    'Jan' => 'Yan', 'Feb' => 'Fev', 'Mar' => 'Mar', 'Apr' => 'Apr',
    'May' => 'May', 'Jun' => 'Iyun', 'Jul' => 'Iyul', 'Aug' => 'Avg',
    'Sep' => 'Sen', 'Oct' => 'Okt', 'Nov' => 'Noy', 'Dec' => 'Dek'
];

// Build list of months for query
$months_to_query = [];
for ($i = 5; $i >= 0; $i--) {
    $months_to_query[] = date('Y-m', strtotime("-$i months"));
}

// Single optimized query for all months
// Use UNION to avoid double-counting when payment_date and created_at differ
$revenue_query = "
    SELECT 
        DATE_FORMAT(payment_date, '%Y-%m') as month_key,
        SUM(amount) as revenue
    FROM payments 
    WHERE DATE_FORMAT(payment_date, '%Y-%m') >= ? 
    GROUP BY month_key
    
    UNION ALL
    
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month_key,
        SUM(amount) as revenue
    FROM payments 
    WHERE payment_date IS NULL 
      AND DATE_FORMAT(created_at, '%Y-%m') >= ?
    GROUP BY month_key
    
    ORDER BY month_key ASC
";

$earliest_month = $months_to_query[0];
$stmt = $conn->prepare($revenue_query);
if (!$stmt) {
    log_statistics_issue('Failed to prepare revenue query', ['error' => $conn->error]);
    // Use empty array as fallback
    $monthly_revenue = array_map(function($month) use ($month_name_map) {
        $month_name_en = date('M', strtotime($month . '-01'));
        $month_name = $month_name_map[$month_name_en] ?? $month_name_en;
        return ['month' => $month_name, 'revenue' => 0];
    }, $months_to_query);
} else {
    $stmt->bind_param('ss', $earliest_month, $earliest_month);
    $stmt->execute();
    $revenue_result = $stmt->get_result();
    
    // Organize results by month
    $revenue_by_month = [];
    while ($row = $revenue_result->fetch_assoc()) {
        $month = $row['month_key'];
        if (!isset($revenue_by_month[$month])) {
            $revenue_by_month[$month] = 0;
        }
        $revenue_by_month[$month] += $row['revenue'];
    }
    $stmt->close();
    
    // Build final array with all months (including those with zero revenue)
    $monthly_revenue = [];
    foreach ($months_to_query as $month) {
        $month_name_en = date('M', strtotime($month . '-01'));
        $month_name = $month_name_map[$month_name_en] ?? $month_name_en;
        
        $revenue = isset($revenue_by_month[$month]) ? $revenue_by_month[$month] : 0;
        
        // Note: Zero revenue is logged for informational purposes, not necessarily an error
        if ($revenue == 0) {
            log_statistics_issue('Zero revenue for month (informational)', ['month' => $month]);
        }
        
        $monthly_revenue[] = [
            'month' => $month_name,
            'revenue' => $revenue
        ];
    }
}

// Eng faol mijozlar (top 5) - with parameterized query
$top_customers_query = "
    SELECT 
        c.ad_name,
        c.phone,
        COUNT(b.id) as total_ads,
        COALESCE(SUM(p.amount), 0) as total_spent
    FROM customers c
    LEFT JOIN bookings b ON c.id = b.customer_id
    LEFT JOIN payments p ON c.id = p.customer_id
    WHERE c.is_active = 1
    GROUP BY c.id, c.ad_name, c.phone
    ORDER BY total_ads DESC
    LIMIT 5
";
$top_customers = $conn->query($top_customers_query);
if (!$top_customers) {
    log_statistics_issue('Failed to fetch top customers', ['error' => $conn->error]);
}

// Paketlar bo'yicha statistika - with parameterized query and logging
$packages_stats_query = "
    SELECT 
        p.name,
        COUNT(cp.id) as total_sold,
        SUM(cp.used_ads) as total_ads_used,
        SUM(cp.total_ads) as total_ads_available
    FROM packages p
    LEFT JOIN customer_packages cp ON p.id = cp.package_id
    WHERE p.is_active = 1
    GROUP BY p.id, p.name
    ORDER BY total_sold DESC
";
$packages_stats = $conn->query($packages_stats_query);
if (!$packages_stats) {
    log_statistics_issue('Failed to fetch packages stats', ['error' => $conn->error]);
}

// To'lov usullari statistikasi - with parameterized query
// Prioritize payment_date, fallback to created_at when payment_date is NULL
$payment_methods_query = "
    SELECT 
        payment_method,
        COUNT(*) as count,
        SUM(amount) as total
    FROM payments
    WHERE (payment_date BETWEEN ? AND ?) 
       OR (payment_date IS NULL AND DATE(created_at) BETWEEN ? AND ?)
    GROUP BY payment_method
";
$stmt = $conn->prepare($payment_methods_query);
if (!$stmt) {
    log_statistics_issue('Failed to prepare payment methods query', ['error' => $conn->error]);
    $payment_methods = []; // Use empty array as fallback
} else {
    $stmt->bind_param('ssss', $date_from, $date_to, $date_from, $date_to);
    $stmt->execute();
    $payment_methods = $stmt->get_result();
    
    // Check for inconsistent data
    $total_by_method = 0;
    $temp_methods = [];
    while ($row = $payment_methods->fetch_assoc()) {
        $total_by_method += $row['total'];
        $temp_methods[] = $row;
        
        // Log if payment method is missing or invalid
        if (empty($row['payment_method'])) {
            log_statistics_issue('Payment with empty payment_method', ['count' => $row['count'], 'total' => $row['total']]);
        }
    }
    
    // Check if total by methods matches overall total revenue
    if (abs($total_by_method - ($stats['total_revenue'] ?? 0)) > REVENUE_MISMATCH_TOLERANCE) {
        log_statistics_issue('Revenue mismatch between methods and total', [
            'total_by_methods' => $total_by_method,
            'total_revenue' => $stats['total_revenue']
        ]);
    }
    
    // Reset result for later use in the page
    $payment_methods = $temp_methods;
    $stmt->close();
}

// Reklama holati statistikasi - with logging
$ads_status_query = "
    SELECT 
        status,
        COUNT(*) as count
    FROM bookings
    GROUP BY status
";
$ads_status_result = $conn->query($ads_status_query);
if (!$ads_status_result) {
    log_statistics_issue('Failed to fetch ads status', ['error' => $conn->error]);
    $ads_status = [];
} else {
    // Store in array for consistency with payment_methods
    $ads_status = [];
    while ($row = $ads_status_result->fetch_assoc()) {
        $ads_status[] = $row;
    }
}

// Log statistics summary
log_statistics_issue('Statistics page loaded successfully', [
    'date_from' => $date_from,
    'date_to' => $date_to,
    'total_revenue' => $stats['total_revenue'] ?? 0,
    'total_payments' => $stats['total_payments'] ?? 0
]);

$page_title = 'Statistika';
include '../components/header.php';
?>

<!-- Main Container -->
<div class="flex">
    
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 mt-16 p-6">
        
        <!-- Page Header - GRADIENT -->
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between mb-6 gap-4">
            <div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                    <i class="fas fa-chart-line mr-3"></i>Statistika
                </h2>
                <p class="text-gray-600 flex items-center gap-2">
                    <i class="fas fa-info-circle text-purple-500"></i>
                    Umumiy hisobotlar va tahlil
                </p>
            </div>
        </div>

        <!-- Date Filter - CHIROYLI CARD -->
        <div class="bg-gradient-to-r from-purple-50 via-pink-50 to-orange-50 rounded-xl shadow-xl p-6 mb-6 border-2 border-purple-200">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-purple-500"></i>
                        Boshlanish
                    </label>
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                           class="w-full px-4 py-3 border-2 border-purple-300 rounded-xl focus:ring-4 focus:ring-purple-200 focus:border-purple-500 shadow-md">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-calendar-check text-purple-500"></i>
                        Tugash
                    </label>
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                           class="w-full px-4 py-3 border-2 border-purple-300 rounded-xl focus:ring-4 focus:ring-purple-200 focus:border-purple-500 shadow-md">
                </div>
                <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-500 to-pink-600 text-white rounded-xl hover:from-purple-600 hover:to-pink-700 transition-all duration-300 font-bold shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-search mr-2"></i>Qidirish
                </button>
                <a href="statistics.php" class="px-8 py-3 bg-white text-gray-700 rounded-xl hover:bg-gray-100 transition-all duration-300 font-bold shadow-lg border-2 border-gray-300">
                    <i class="fas fa-redo mr-2"></i>Tozalash
                </a>
            </form>
        </div>

        <!-- Stats Cards - GRADIENT -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            
            <!-- Total Revenue -->
            <div class="relative bg-gradient-to-br from-green-400 to-green-600 rounded-xl p-6 text-white shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-money-bill-wave text-3xl"></i>
                        </div>
                        <span class="text-xs bg-white bg-opacity-30 px-3 py-1 rounded-full font-semibold backdrop-blur-sm">Daromad</span>
                    </div>
                    <h3 class="text-4xl font-bold mb-2"><?php echo number_format($stats['total_revenue'] ?? 0, 0, '.', ' '); ?></h3>
                    <p class="text-sm opacity-90 font-semibold">So'm</p>
                </div>
            </div>

            <!-- Active Customers -->
            <div class="relative bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl p-6 text-white shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-users text-3xl"></i>
                        </div>
                        <span class="text-xs bg-white bg-opacity-30 px-3 py-1 rounded-full font-semibold backdrop-blur-sm">Mijozlar</span>
                    </div>
                    <h3 class="text-4xl font-bold mb-2"><?php echo $stats['active_customers']; ?></h3>
                    <p class="text-sm opacity-90 font-semibold">Faol / <?php echo $stats['total_customers']; ?> Jami</p>
                </div>
            </div>

            <!-- Published Ads -->
            <div class="relative bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl p-6 text-white shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-ad text-3xl"></i>
                        </div>
                        <span class="text-xs bg-white bg-opacity-30 px-3 py-1 rounded-full font-semibold backdrop-blur-sm">Reklamalar</span>
                    </div>
                    <h3 class="text-4xl font-bold mb-2"><?php echo $stats['published_ads']; ?></h3>
                    <p class="text-sm opacity-90 font-semibold">Chop etilgan</p>
                </div>
            </div>

            <!-- Active Packages -->
            <div class="relative bg-gradient-to-br from-orange-400 to-orange-600 rounded-xl p-6 text-white shadow-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-16 -mt-16"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-14 h-14 bg-white bg-opacity-30 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <i class="fas fa-box text-3xl"></i>
                        </div>
                        <span class="text-xs bg-white bg-opacity-30 px-3 py-1 rounded-full font-semibold backdrop-blur-sm">Paketlar</span>
                    </div>
                    <h3 class="text-4xl font-bold mb-2"><?php echo $stats['active_packages']; ?></h3>
                    <p class="text-sm opacity-90 font-semibold">Faol paketlar</p>
                </div>
            </div>

        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Revenue Chart -->
            <div class="bg-gradient-to-br from-white via-purple-50 to-pink-50 rounded-xl shadow-xl p-6 border-2 border-purple-200 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-chart-line text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Oylik Daromad</h3>
                        <p class="text-sm text-gray-600">So'mda</p>
                    </div>
                </div>
                <canvas id="revenueChart" class="w-full" style="max-height: 300px;"></canvas>
            </div>

            <!-- Ads Status Chart -->
            <div class="bg-gradient-to-br from-white via-blue-50 to-cyan-50 rounded-xl shadow-xl p-6 border-2 border-blue-200 hover:shadow-2xl transition-all duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-chart-pie text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Reklama Holati</h3>
                        <p class="text-sm text-gray-600">Barcha reklamalar</p>
                    </div>
                </div>
                <canvas id="adsStatusChart" class="w-full" style="max-height: 300px;"></canvas>
            </div>

        </div>

        <!-- Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Top Customers -->
            <div class="bg-gradient-to-br from-white via-yellow-50 to-orange-50 rounded-xl shadow-xl p-6 border-2 border-yellow-200">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-trophy text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Eng Faol Mijozlar</h3>
                        <p class="text-sm text-gray-600">Top 5</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <?php 
                    $rank = 1; 
                    $rank_colors = [
                        1 => 'from-yellow-400 to-yellow-600',
                        2 => 'from-gray-300 to-gray-500',
                        3 => 'from-orange-400 to-orange-600',
                    ];
                    while($customer = $top_customers->fetch_assoc()): 
                        $color = $rank_colors[$rank] ?? 'from-purple-500 to-pink-600';
                    ?>
                    <div class="flex items-center justify-between p-4 bg-white rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-102 border-2 border-gray-200">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-r <?php echo $color; ?> rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                <?php echo $rank++; ?>
                            </div>
                            <div>
                                <p class="font-bold text-gray-800"><?php echo htmlspecialchars($customer['ad_name']); ?></p>
                                <p class="text-sm text-gray-500 flex items-center gap-1">
                                    <i class="fas fa-phone text-xs"></i>
                                    <?php echo htmlspecialchars($customer['phone']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-lg text-purple-600"><?php echo $customer['total_ads']; ?> ta</p>
                            <p class="text-xs text-gray-500"><?php echo number_format($customer['total_spent'], 0, '.', ' '); ?> so'm</p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="bg-gradient-to-br from-white via-green-50 to-emerald-50 rounded-xl shadow-xl p-6 border-2 border-green-200">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-credit-card text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">To'lov Usullari</h3>
                        <p class="text-sm text-gray-600">Tanlangan davr</p>
                    </div>
                </div>
                <canvas id="paymentMethodsChart" class="w-full" style="max-height: 300px;"></canvas>
            </div>

        </div>

        <!-- Packages Stats -->
        <div class="bg-gradient-to-br from-white via-orange-50 to-red-50 rounded-xl shadow-xl p-6 border-2 border-orange-200">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-box-open text-xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Paketlar Statistikasi</h3>
                    <p class="text-sm text-gray-600">Barcha paketlar</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-orange-100 to-red-100">
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">
                                <i class="fas fa-box mr-2 text-orange-500"></i>Paket Nomi
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">
                                <i class="fas fa-shopping-cart mr-2 text-green-500"></i>Sotilgan
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">
                                <i class="fas fa-check-circle mr-2 text-blue-500"></i>Ishlatilgan
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">
                                <i class="fas fa-list mr-2 text-purple-500"></i>Jami
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-700">
                                <i class="fas fa-percentage mr-2 text-pink-500"></i>Foiz
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($pkg = $packages_stats->fetch_assoc()): 
                            $percentage = $pkg['total_ads_available'] > 0 ? 
                                        round(($pkg['total_ads_used'] / $pkg['total_ads_available']) * 100) : 0;
                        ?>
                        <tr class="border-b hover:bg-white transition-all duration-300">
                            <td class="px-6 py-4 font-bold text-gray-800"><?php echo htmlspecialchars($pkg['name']); ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full font-bold">
                                    <?php echo $pkg['total_sold']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold">
                                    <?php echo $pkg['total_ads_used']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full font-bold">
                                    <?php echo $pkg['total_ads_available']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <div class="w-32 bg-gray-200 rounded-full h-3 shadow-inner">
                                        <div class="bg-gradient-to-r from-orange-400 via-pink-500 to-red-500 h-3 rounded-full transition-all duration-500" 
                                             style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 min-w-[45px]"><?php echo $percentage; ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</div>

<?php include '../components/footer.php'; ?>

<script>
    // Revenue Chart - GRADIENT
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueGradient = revenueCtx.createLinearGradient(0, 0, 0, 300);
    revenueGradient.addColorStop(0, 'rgba(147, 51, 234, 0.3)');
    revenueGradient.addColorStop(1, 'rgba(236, 72, 153, 0.05)');

    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthly_revenue, 'month')); ?>,
            datasets: [{
                label: 'Daromad (So\'m)',
                data: <?php echo json_encode(array_column($monthly_revenue, 'revenue')); ?>,
                borderColor: 'rgb(147, 51, 234)',
                backgroundColor: revenueGradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(147, 51, 234)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true,
                    labels: {
                        font: { size: 14, weight: 'bold' },
                        color: '#374151'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                }
            }
        }
    });

    // Ads Status Chart - DOUGHNUT
    const adsStatusCtx = document.getElementById('adsStatusChart').getContext('2d');
    new Chart(adsStatusCtx, {
        type: 'doughnut',
        data: {
            labels: [
                <?php 
                $labels = [];
                foreach($ads_status as $row) {
                    $status_map = [
                        'scheduled' => 'Rejalashtirilgan',
                        'published' => 'Chop etilgan',
                        'cancelled' => 'Bekor qilingan'
                    ];
                    $labels[] = "'" . ($status_map[$row['status']] ?? $row['status']) . "'";
                }
                echo implode(',', $labels);
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    $data = [];
                    foreach($ads_status as $row) {
                        $data[] = $row['count'];
                    }
                    echo implode(',', $data);
                    ?>
                ],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(239, 68, 68, 0.8)'
                ],
                borderWidth: 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom',
                    labels: {
                        font: { size: 13, weight: 'bold' },
                        padding: 15,
                        color: '#374151'
                    }
                }
            }
        }
    });

    // Payment Methods Chart - BAR
    const paymentCtx = document.getElementById('paymentMethodsChart').getContext('2d');
    new Chart(paymentCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php 
                $labels = [];
                foreach($payment_methods as $row) {
                    $method_map = [
                        'naqd' => 'Naqd',
                        'karta' => 'Karta',
                        'nasiya' => 'Nasiya',
                        'bepul' => 'Bepul'
                    ];
                    $labels[] = "'" . ($method_map[$row['payment_method']] ?? $row['payment_method']) . "'";
                }
                echo implode(',', $labels);
                ?>
            ],
            datasets: [{
                label: 'So\'m',
                data: [
                    <?php 
                    $data = [];
                    foreach($payment_methods as $row) {
                        $data[] = $row['total'];
                    }
                    echo implode(',', $data);
                    ?>
                ],
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
                    'rgba(168, 85, 247, 0.8)'
                ],
                borderWidth: 0,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' }
                }
            }
        }
    });
</script>

</body>
</html>