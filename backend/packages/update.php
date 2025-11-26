<?php
/**
 * Update Package
 * Paket reklama sonini tahrirlash
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
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$new_total_ads = isset($_POST['total_ads']) ? intval($_POST['total_ads']) : 0;

// Validatsiya
if ($id <= 0) {
    send_error('ID noto\'g\'ri');
}

if ($new_total_ads <= 0 || $new_total_ads > 100) {
    send_error('Reklama soni 1 dan 100 gacha bo\'lishi kerak');
}

try {
    // Paket mavjudligini va hozirgi holatini tekshirish
    $check_stmt = $conn->prepare("
        SELECT 
            cp.id,
            cp.total_ads as old_total_ads,
            cp.used_ads,
            cp.remaining_ads,
            c.ad_name
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
    
    // Ishlatilgan reklamadan kam bo'lishi mumkin emas
    if ($new_total_ads < $package['used_ads']) {
        send_error('Yangi reklama soni ishlatilgan reklamalar sonidan (' . $package['used_ads'] . ') kam bo\'lishi mumkin emas');
    }
    
    // Yangi remaining_ads hisoblash
    $new_remaining_ads = $new_total_ads - $package['used_ads'];
    
    // Yangi status aniqlash
    $new_status = $new_remaining_ads > 0 ? 'active' : 'completed';
    
    // Transaction boshlash
    $conn->begin_transaction();
    
    try {
        // 1. Customer package yangilash
        $update_cp = $conn->prepare("
            UPDATE customer_packages 
            SET 
                total_ads = ?,
                remaining_ads = ?,
                status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        $update_cp->bind_param("iisi", $new_total_ads, $new_remaining_ads, $new_status, $id);
        
        if (!$update_cp->execute()) {
            throw new Exception('Paketni yangilashda xatolik');
        }
        
        // 2. Base package'ni ham yangilash (agar kerak bo'lsa)
        $update_package = $conn->prepare("
            UPDATE packages 
            SET 
                total_ads = ?,
                updated_at = NOW()
            WHERE id = (SELECT package_id FROM customer_packages WHERE id = ?)
        ");
        
        $update_package->bind_param("ii", $new_total_ads, $id);
        $update_package->execute();
        
        // Commit
        $conn->commit();
        
        // Log qilish
        error_log("Package updated: ID=$id, Old={$package['old_total_ads']}, New=$new_total_ads, Remaining=$new_remaining_ads");
        
        send_success([
            'package_id' => $id,
            'customer_name' => $package['ad_name'],
            'old_total_ads' => (int)$package['old_total_ads'],
            'new_total_ads' => $new_total_ads,
            'used_ads' => (int)$package['used_ads'],
            'new_remaining_ads' => $new_remaining_ads,
            'new_status' => $new_status,
            'difference' => $new_total_ads - $package['old_total_ads']
        ], 'Paket muvaffaqiyatli yangilandi');
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Update package error: " . $e->getMessage());
    send_error('Paketni yangilashda xatolik: ' . $e->getMessage());
}

$conn->close();
?>