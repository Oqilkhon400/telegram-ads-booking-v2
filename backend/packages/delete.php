<?php
/**
 * Delete Package
 * Paketni o'chirish
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

// ID olish (customer_package_id)
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    send_error('ID noto\'g\'ri');
}

try {
    // Paket mavjudligini tekshirish
    $check_stmt = $conn->prepare("
        SELECT 
            cp.id,
            cp.customer_id,
            c.ad_name,
            cp.used_ads,
            cp.remaining_ads
        FROM customer_packages cp
        JOIN customers c ON cp.customer_id = c.id
        WHERE cp.id = ? 
        LIMIT 1
    ");
    
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        send_error('Paket topilmadi', 404);
    }
    
    $package = $result->fetch_assoc();
    
    // Agar paket ishlatilgan bo'lsa (used_ads > 0), o'chirish mumkin emas
    if ($package['used_ads'] > 0) {
        send_error('Bu paket allaqachon ishlatilgan. O\'chirish mumkin emas.');
    }
    
    // Rejalashtirilgan bookinglar borligini tekshirish
    $bookings_check = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE customer_package_id = ? AND status = 'scheduled'
    ");
    
    $bookings_check->bind_param("i", $id);
    $bookings_check->execute();
    $booking_count = $bookings_check->get_result()->fetch_assoc()['count'];
    
    if ($booking_count > 0) {
        send_error('Bu paketda rejalashtirilgan reklamalar mavjud. Avval ularni bekor qilish kerak.');
    }
    
    // Transaction boshlash
    $conn->begin_transaction();
    
    try {
        // 1. To'lovlarni o'chirish
        $delete_payments = $conn->prepare("DELETE FROM payments WHERE customer_package_id = ?");
        $delete_payments->bind_param("i", $id);
        $delete_payments->execute();
        
        // 2. Customer package o'chirish (CASCADE orqali bookinglar ham o'chadi)
        $delete_cp = $conn->prepare("DELETE FROM customer_packages WHERE id = ?");
        $delete_cp->bind_param("i", $id);
        $delete_cp->execute();
        
        // 3. Agar package boshqa joyda ishlatilmayotgan bo'lsa, o'chirish
        $check_usage = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM customer_packages 
            WHERE package_id = (SELECT package_id FROM customer_packages WHERE id = ?)
        ");
        $check_usage->bind_param("i", $id);
        $check_usage->execute();
        $usage_count = $check_usage->get_result()->fetch_assoc()['count'];
        
        if ($usage_count == 0) {
            // Package ham o'chirish
            $delete_package = $conn->prepare("
                DELETE FROM packages 
                WHERE id = (SELECT package_id FROM customer_packages WHERE id = ?)
            ");
            $delete_package->bind_param("i", $id);
            $delete_package->execute();
        }
        
        // Commit
        $conn->commit();
        
        send_success([
            'deleted_id' => $id,
            'customer_name' => $package['ad_name']
        ], 'Paket muvaffaqiyatli o\'chirildi');
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Delete package error: " . $e->getMessage());
    send_error('Paketni o\'chirishda xatolik: ' . $e->getMessage());
}

$conn->close();
?>