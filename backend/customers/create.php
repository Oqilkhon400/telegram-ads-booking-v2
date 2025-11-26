<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once '../../config/database.php';
require_once '../../config/settings.php';

header('Content-Type: application/json; charset=utf-8');

// Session tekshirish
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Login qiling']);
    exit;
}

// POST tekshirish
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST kerak']);
    exit;
}

// Ma'lumotlarni olish
$ad_name = trim($_POST['ad_name'] ?? '');
$contact_person = trim($_POST['contact_person'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// Validatsiya
if (empty($ad_name)) {
    echo json_encode(['success' => false, 'error' => 'Reklama nomini kiriting']);
    exit;
}

if (empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Telefon kiriting']);
    exit;
}

try {
    // Telefon tekshirish
    $check_stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ?");
    $check_stmt->bind_param("s", $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Bu telefon mavjud']);
        exit;
    }
    
    // Qo'shish
    $stmt = $conn->prepare("INSERT INTO customers (ad_name, contact_person, phone) VALUES (?, ?, ?)");
    $contact = $contact_person ?: null;
    $stmt->bind_param("sss", $ad_name, $contact, $phone);
    
    if ($stmt->execute()) {
        $customer_id = $conn->insert_id;
        
        // Yangi mijoz ma'lumotlarini olish
        $result = $conn->query("SELECT * FROM customers WHERE id = $customer_id");
        $customer = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'data' => $customer,
            'message' => 'Mijoz qo\'shildi'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Bazaga yozishda xato: ' . $stmt->error
        ]);
    }
    
} catch (Exception $e) {
    error_log("Customer create error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Xatolik: ' . $e->getMessage()
    ]);
}

$conn->close();
?>