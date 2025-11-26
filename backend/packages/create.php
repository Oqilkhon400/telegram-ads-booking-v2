<?php
/**
 * Create Package with Payment
 * Yangi paket va to'lov yaratish (nasiya support bilan)
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

// Session tekshirish
if (!is_logged_in()) {
    send_error('Tizimga kirish kerak', 401);
}

// Faqat POST metodini qabul qilish
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_error('Faqat POST metodi qabul qilinadi', 405);
}

// Ma'lumotlarni olish
$customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0;
$total_ads = isset($_POST['total_ads']) ? intval($_POST['total_ads']) : 0;
$price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
$payment_method = isset($_POST['payment_method']) ? clean_input($_POST['payment_method']) : 'naqd';
$notes = isset($_POST['notes']) ? clean_input($_POST['notes']) : null;

$created_by = $_SESSION['user_id'];

// Validatsiya
$errors = [];

if ($customer_id <= 0) {
    $errors[] = 'Mijoz tanlanmagan';
}

if ($total_ads <= 0 || $total_ads > 100) {
    $errors[] = 'Reklama soni 1 dan 100 gacha bo\'lishi kerak';
}

if ($price < 0) {
    $errors[] = 'Narx manfiy bo\'lishi mumkin emas';
}

if (!in_array($payment_method, ['naqd', 'karta', 'nasiya', 'bepul'])) {
    $errors[] = 'To\'lov turi noto\'g\'ri';
}

// Bepul bo'lsa price 0
if ($payment_method === 'bepul') {
    $price = 0;
}

// Agar bepul bo'lmasa va price 0 bo'lsa xato
if ($payment_method !== 'bepul' && $price <= 0) {
    $errors[] = 'To\'lov summasi kiritilishi kerak';
}

if (!empty($errors)) {
    send_error(implode(', ', $errors));
}

// Mijoz mavjudligini tekshirish
$customer_check = $conn->prepare("SELECT id, ad_name FROM customers WHERE id = ? LIMIT 1");
$customer_check->bind_param("i", $customer_id);
$customer_check->execute();
$customer_result = $customer_check->get_result();

if ($customer_result->num_rows === 0) {
    send_error('Mijoz topilmadi', 404);
}

$customer = $customer_result->fetch_assoc();

// Transaction boshlash
$conn->begin_transaction();

try {
    // 1. Package yaratish (package name bo'sh qoladi, chunki kerak emas)
    $package_stmt = $conn->prepare("
        INSERT INTO packages (name, total_ads, price, is_active)
        VALUES ('', ?, ?, 1)
    ");
    
    $package_stmt->bind_param("id", $total_ads, $price);
    
    if (!$package_stmt->execute()) {
        throw new Exception('Paket yaratishda xatolik');
    }
    
    $package_id = $conn->insert_id;
    
    // 2. Customer Package yaratish
    $customer_package_stmt = $conn->prepare("
        INSERT INTO customer_packages (customer_id, package_id, total_ads, used_ads, remaining_ads, status)
        VALUES (?, ?, ?, 0, ?, 'active')
    ");
    
    $customer_package_stmt->bind_param("iiii", $customer_id, $package_id, $total_ads, $total_ads);
    
    if (!$customer_package_stmt->execute()) {
        throw new Exception('Mijoz paketini yaratishda xatolik');
    }
    
    $customer_package_id = $conn->insert_id;
    
    // 3. Payment yaratish (avtomatik bugungi sana)
    $payment_date = date('Y-m-d');
    
    $payment_stmt = $conn->prepare("
        INSERT INTO payments (customer_id, customer_package_id, amount, payment_date, payment_method, notes, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $payment_stmt->bind_param("iidsssi", 
        $customer_id, 
        $customer_package_id, 
        $price, 
        $payment_date, 
        $payment_method, 
        $notes, 
        $created_by
    );
    
    if (!$payment_stmt->execute()) {
        throw new Exception('To\'lovni yaratishda xatolik');
    }
    
    $payment_id = $conn->insert_id;
    
    // Transaction commit
    $conn->commit();
    
    // Success response
    send_success([
        'package_id' => $package_id,
        'customer_package_id' => $customer_package_id,
        'payment_id' => $payment_id,
        'customer_name' => $customer['ad_name'],
        'total_ads' => $total_ads,
        'price' => format_money($price),
        'payment_method' => $payment_method,
        'payment_date' => format_date($payment_date),
        'is_nasiya' => $payment_method === 'nasiya'
    ], 'Paket muvaffaqiyatli yaratildi');
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    error_log("Create package error: " . $e->getMessage());
    send_error('Paket yaratishda xatolik: ' . $e->getMessage());
}

$conn->close();
?>