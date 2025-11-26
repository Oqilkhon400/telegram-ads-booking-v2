<?php
/**
 * Sozlamalar Sahifasi
 * Profil, parol va tizim sozlamalari - ZAMONAVIY VA XAVFSIZ
 */

require_once '../../config/database.php';

// Session va autentifikatsiya
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Foydalanuvchi ma'lumotlarini olish - XAVFSIZ
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Profil yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    
    // Username mavjudligini tekshirish - XAVFSIZ
    $check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param('si', $username, $user_id);
    $stmt->execute();
    $check_result = $stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_message = "Bu username allaqachon band!";
    } else {
        $update_query = "UPDATE users SET full_name = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('ssi', $full_name, $username, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $full_name;
            $success_message = "Profil muvaffaqiyatli yangilandi!";
            
            // Yangi ma'lumotlarni olish
            $stmt = $conn->prepare($user_query);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $user = $user_result->fetch_assoc();
        } else {
            $error_message = "Xatolik yuz berdi!";
        }
    }
}

// Parol o'zgartirish
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Joriy parolni tekshirish
    if (!password_verify($current_password, $user['password'])) {
        $error_message = "Joriy parol noto'g'ri!";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Yangi parollar mos emas!";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Parol kamida 6 belgidan iborat bo'lishi kerak!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('si', $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Parol muvaffaqiyatli o'zgartirildi!";
        } else {
            $error_message = "Xatolik yuz berdi!";
        }
    }
}

// Tizim sozlamalari (faqat superadmin)
$settings = [];
if ($user['role'] === 'superadmin') {
    $settings_query = "SELECT * FROM settings LIMIT 1";
    $settings_result = $conn->query($settings_query);
    
    if ($settings_result && $settings_result->num_rows > 0) {
        $settings = $settings_result->fetch_assoc();
    }
}

$page_title = 'Sozlamalar';
include '../components/header.php';
?>

<!-- Main Container -->
<div class="flex">
    
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 mt-16 p-6">
        
        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
        <div class="mb-6 bg-gradient-to-r from-green-400 to-green-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 animate-fade-in">
            <i class="fas fa-check-circle text-2xl"></i>
            <span class="font-semibold"><?php echo $success_message; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="mb-6 bg-gradient-to-r from-red-400 to-red-600 text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3 animate-fade-in">
            <i class="fas fa-exclamation-circle text-2xl"></i>
            <span class="font-semibold"><?php echo $error_message; ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="mb-6">
            <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent mb-2">
                <i class="fas fa-cog mr-3"></i>Sozlamalar
            </h2>
            <p class="text-gray-600 flex items-center gap-2">
                <i class="fas fa-info-circle text-blue-500"></i>
                Profil va tizim sozlamalari
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Sidebar Menu -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-xl p-4 border-2 border-blue-200 sticky top-20">
                    <nav class="space-y-2">
                        <button onclick="showTab('profile')" id="tab-profile" 
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
                            <i class="fas fa-user text-lg"></i>
                            <span class="font-semibold">Profil</span>
                        </button>
                        <button onclick="showTab('password')" id="tab-password" 
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-lock text-lg"></i>
                            <span class="font-semibold">Parol</span>
                        </button>
                        <?php if ($user['role'] === 'superadmin'): ?>
                        <button onclick="showTab('system')" id="tab-system" 
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-server text-lg"></i>
                            <span class="font-semibold">Tizim</span>
                        </button>
                        <button onclick="showTab('telegram')" id="tab-telegram" 
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-700 hover:bg-gray-100">
                            <i class="fab fa-telegram text-lg"></i>
                            <span class="font-semibold">Telegram</span>
                        </button>
                        <button onclick="showTab('sync')" id="tab-sync" 
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sync-alt text-lg"></i>
                            <span class="font-semibold">Sinxronlash</span>
                        </button>
                        <button onclick="showTab('danger')" id="tab-danger" 
                                class="w-full flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-300 text-red-600 hover:bg-red-50">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                            <span class="font-semibold">Xavfli zona</span>
                        </button>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>

            <!-- Content Area -->
            <div class="lg:col-span-3">
                
                <!-- Profile Tab -->
                <div id="content-profile" class="tab-content">
                    <div class="bg-gradient-to-br from-white via-blue-50 to-purple-50 rounded-xl shadow-xl p-6 border-2 border-blue-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-user-edit text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Profil Ma'lumotlari</h3>
                                <p class="text-sm text-gray-600">Shaxsiy ma'lumotlarni yangilash</p>
                            </div>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">To'liq Ism</label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required 
                                       class="w-full px-4 py-3 border-2 border-blue-300 rounded-xl focus:ring-4 focus:ring-blue-200 focus:border-blue-500 shadow-md">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required 
                                       class="w-full px-4 py-3 border-2 border-blue-300 rounded-xl focus:ring-4 focus:ring-blue-200 focus:border-blue-500 shadow-md">
                            </div>

                            <div class="bg-gray-50 rounded-xl p-4 border">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Role:</span>
                                    <span class="font-bold text-blue-600"><?php echo ucfirst($user['role']); ?></span>
                                </div>
                                <div class="flex justify-between text-sm mt-2">
                                    <span class="text-gray-600">Ro'yxatdan o'tgan:</span>
                                    <span class="font-bold"><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></span>
                                </div>
                            </div>

                            <div class="pt-4">
                                <button type="submit" name="update_profile" class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl font-bold hover:from-blue-600 hover:to-purple-700 transition-all shadow-lg">
                                    <i class="fas fa-save mr-2"></i>Saqlash
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password Tab -->
                <div id="content-password" class="tab-content hidden">
                    <div class="bg-gradient-to-br from-white via-yellow-50 to-orange-50 rounded-xl shadow-xl p-6 border-2 border-yellow-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-key text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Parolni O'zgartirish</h3>
                                <p class="text-sm text-gray-600">Xavfsizlik uchun parolni yangilang</p>
                            </div>
                        </div>
                        
                        <form method="POST" class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Joriy Parol</label>
                                <input type="password" name="current_password" required 
                                       class="w-full px-4 py-3 border-2 border-yellow-300 rounded-xl focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 shadow-md">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Yangi Parol</label>
                                <input type="password" name="new_password" required minlength="6"
                                       class="w-full px-4 py-3 border-2 border-yellow-300 rounded-xl focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 shadow-md">
                                <p class="text-sm text-gray-600 mt-1">Kamida 6 belgidan iborat bo'lishi kerak</p>
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Parolni Tasdiqlang</label>
                                <input type="password" name="confirm_password" required minlength="6"
                                       class="w-full px-4 py-3 border-2 border-yellow-300 rounded-xl focus:ring-4 focus:ring-yellow-200 focus:border-yellow-500 shadow-md">
                            </div>

                            <div class="pt-4">
                                <button type="submit" name="change_password" class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-xl font-bold hover:from-yellow-600 hover:to-orange-700 transition-all shadow-lg">
                                    <i class="fas fa-lock mr-2"></i>Parolni O'zgartirish
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($user['role'] === 'superadmin'): ?>
                <!-- System Tab -->
                <div id="content-system" class="tab-content hidden">
                    <div class="bg-gradient-to-br from-white via-purple-50 to-pink-50 rounded-xl shadow-xl p-6 border-2 border-purple-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-server text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Tizim Sozlamalari</h3>
                                <p class="text-sm text-gray-600">Server va database ma'lumotlari</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-white rounded-xl p-4 border-2 border-purple-200">
                                <h4 class="font-bold text-gray-800 mb-3">Server Ma'lumotlari</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between p-2 bg-purple-50 rounded-lg">
                                        <span class="text-gray-600">PHP Versiya:</span>
                                        <span class="font-bold"><?php echo PHP_VERSION; ?></span>
                                    </div>
                                    <div class="flex justify-between p-2 bg-purple-50 rounded-lg">
                                        <span class="text-gray-600">Server:</span>
                                        <span class="font-bold"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                                    </div>
                                    <div class="flex justify-between p-2 bg-purple-50 rounded-lg">
                                        <span class="text-gray-600">Hozirgi vaqt:</span>
                                        <span class="font-bold"><?php echo date('d.m.Y H:i:s'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                                    <div>
                                        <h4 class="font-bold text-yellow-900">Backup Eslatma</h4>
                                        <p class="text-sm text-yellow-800">Database backup'ni muntazam oling.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Telegram Tab -->
                <div id="content-telegram" class="tab-content hidden">
                    <div class="bg-gradient-to-br from-white via-blue-50 to-cyan-50 rounded-xl shadow-xl p-6 border-2 border-blue-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fab fa-telegram text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Telegram Bot</h3>
                                <p class="text-sm text-gray-600">Bot sozlamalari</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl p-6 text-white">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-lg font-bold mb-1">Bot Holati</h4>
                                        <div class="flex items-center gap-2">
                                            <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                            <span class="font-bold">Faol</span>
                                        </div>
                                    </div>
                                    <i class="fab fa-telegram text-6xl opacity-20"></i>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl p-4 border-2 border-blue-200">
                                <h4 class="font-bold text-gray-800 mb-3">Webhook URL</h4>
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <code class="text-sm text-gray-700">https://reklama.kosonsoyliklar.uz/telegram/webhook.php</code>
                                    <button onclick="copyWebhook()" class="px-3 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600 text-sm">
                                        <i class="fas fa-copy mr-1"></i>Nusxa
                                    </button>
                                </div>
                            </div>

                            <a href="https://t.me/kosonsoy_bot" target="_blank" 
                               class="block w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white text-center rounded-xl font-bold hover:from-blue-600 hover:to-cyan-700 transition shadow-lg">
                                <i class="fab fa-telegram mr-2"></i>Bot'ni Ochish
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sync Tab -->
                <div id="content-sync" class="tab-content hidden">
                    <div class="bg-gradient-to-br from-white via-green-50 to-teal-50 rounded-xl shadow-xl p-6 border-2 border-green-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-sync-alt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Ma'lumotlarni Sinxronlash</h3>
                                <p class="text-sm text-gray-600">Paketlar va bookinglar o'rtasidagi moslikni tekshirish</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                                    <div>
                                        <h4 class="font-bold text-blue-900">Bu nima qiladi?</h4>
                                        <p class="text-sm text-blue-800">
                                            Paketlardagi <strong>ishlatilgan reklamalar soni</strong> va 
                                            <strong>calendardagi bookinglar soni</strong> bir xil bo'lishi kerak.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <button onclick="checkSync()" class="py-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 font-bold shadow-lg">
                                    <i class="fas fa-search text-xl mb-2 block"></i>
                                    Tekshirish
                                </button>
                                <button onclick="runSync()" class="py-4 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-xl hover:from-green-600 hover:to-teal-700 font-bold shadow-lg">
                                    <i class="fas fa-wrench text-xl mb-2 block"></i>
                                    Tuzatish
                                </button>
                            </div>

                            <div id="syncResults" class="hidden"></div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone Tab -->
                <div id="content-danger" class="tab-content hidden">
                    <div class="bg-gradient-to-br from-white via-red-50 to-orange-50 rounded-xl shadow-xl p-6 border-2 border-red-200">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-600 rounded-xl flex items-center justify-center text-white shadow-lg">
                                <i class="fas fa-exclamation-triangle text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-red-800">Xavfli Zona</h3>
                                <p class="text-sm text-red-600">Test ma'lumotlarini o'chirish</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="bg-red-100 border-2 border-red-300 rounded-xl p-4">
                                <div class="flex items-start gap-3">
                                    <i class="fas fa-skull-crossbones text-red-600 text-2xl"></i>
                                    <div>
                                        <h4 class="font-bold text-red-900">DIQQAT!</h4>
                                        <p class="text-sm text-red-800">
                                            Bu amallar <strong>qaytarib bo'lmaydi!</strong> Faqat test rejimida ishlating.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-xl p-4 border-2 border-gray-200">
                                <h4 class="font-bold text-gray-800 mb-3">Hozirgi ma'lumotlar</h4>
                                <div id="currentStats" class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <div class="text-center p-3 bg-blue-50 rounded-lg">
                                        <i class="fas fa-users text-blue-500 text-xl mb-1"></i>
                                        <p class="text-2xl font-bold text-blue-600" id="statCustomers">-</p>
                                        <p class="text-xs text-gray-600">Mijozlar</p>
                                    </div>
                                    <div class="text-center p-3 bg-green-50 rounded-lg">
                                        <i class="fas fa-box text-green-500 text-xl mb-1"></i>
                                        <p class="text-2xl font-bold text-green-600" id="statPackages">-</p>
                                        <p class="text-xs text-gray-600">Paketlar</p>
                                    </div>
                                    <div class="text-center p-3 bg-purple-50 rounded-lg">
                                        <i class="fas fa-calendar-check text-purple-500 text-xl mb-1"></i>
                                        <p class="text-2xl font-bold text-purple-600" id="statBookings">-</p>
                                        <p class="text-xs text-gray-600">Bookinglar</p>
                                    </div>
                                    <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                        <i class="fas fa-money-bill text-yellow-500 text-xl mb-1"></i>
                                        <p class="text-2xl font-bold text-yellow-600" id="statPayments">-</p>
                                        <p class="text-xs text-gray-600">To'lovlar</p>
                                    </div>
                                </div>
                                <button onclick="loadCurrentStats()" class="mt-3 w-full py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                    <i class="fas fa-sync-alt mr-1"></i>Yangilash
                                </button>
                            </div>

                            <div class="bg-white rounded-xl p-4 border-2 border-red-200">
                                <h4 class="font-bold text-gray-800 mb-4">O'chirish variantlari</h4>
                                
                                <div class="space-y-3">
                                    <button onclick="confirmDelete('bookings')" 
                                            class="w-full p-4 bg-yellow-50 border-2 border-yellow-300 rounded-xl text-left hover:bg-yellow-100 transition">
                                        <p class="font-bold text-yellow-800">
                                            <i class="fas fa-calendar-times mr-2"></i>Faqat Bookinglarni o'chirish
                                        </p>
                                        <p class="text-sm text-yellow-700 mt-1">Bookinglar o'chiriladi, mijozlar va paketlar qoladi</p>
                                    </button>
                                    
                                    <button onclick="confirmDelete('packages')" 
                                            class="w-full p-4 bg-orange-50 border-2 border-orange-300 rounded-xl text-left hover:bg-orange-100 transition">
                                        <p class="font-bold text-orange-800">
                                            <i class="fas fa-box-open mr-2"></i>Paketlar va To'lovlarni o'chirish
                                        </p>
                                        <p class="text-sm text-orange-700 mt-1">Paketlar va to'lovlar o'chiriladi, mijozlar qoladi</p>
                                    </button>
                                    
                                    <button onclick="confirmDelete('templates')" 
                                            class="w-full p-4 bg-purple-50 border-2 border-purple-300 rounded-xl text-left hover:bg-purple-100 transition">
                                        <p class="font-bold text-purple-800">
                                            <i class="fas fa-cubes mr-2"></i>Shablon paketlarni o'chirish
                                        </p>
                                        <p class="text-sm text-purple-700 mt-1">Statistikadagi bo'sh paket shablonlari o'chiriladi</p>
                                    </button>
                                    
                                    <button onclick="confirmDelete('all')" 
                                            class="w-full p-4 bg-red-50 border-2 border-red-400 rounded-xl text-left hover:bg-red-100 transition">
                                        <p class="font-bold text-red-800">
                                            <i class="fas fa-skull mr-2"></i>HAMMASINI O'CHIRISH
                                        </p>
                                        <p class="text-sm text-red-700 mt-1">Barcha mijozlar, paketlar, bookinglar, to'lovlar - HAMMASI!</p>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </main>

</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        <div class="bg-gradient-to-r from-red-500 to-orange-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold"><i class="fas fa-exclamation-triangle mr-2"></i>Tasdiqlash</h3>
        </div>
        
        <div class="p-6">
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-4">
                <p class="text-red-800 font-semibold" id="deleteConfirmText"></p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-1"></i>Parolingizni kiriting
                </label>
                <input type="password" id="confirmPassword" placeholder="Parol..."
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>
            
            <input type="hidden" id="deleteAction">
            
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 font-semibold">
                    Bekor qilish
                </button>
                <button onclick="executeDelete()" class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-orange-600 text-white rounded-xl hover:from-red-600 hover:to-orange-700 font-semibold">
                    <i class="fas fa-trash mr-2"></i>O'chirish
                </button>
            </div>
        </div>
    </div>
</div>

<?php include '../components/footer.php'; ?>

<script>
// TAB FUNCTIONS
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('[id^="tab-"]').forEach(btn => {
        btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-purple-600', 'text-white', 'shadow-lg');
        btn.classList.add('text-gray-700', 'hover:bg-gray-100');
    });
    
    // Show selected tab
    const contentEl = document.getElementById('content-' + tabName);
    if (contentEl) {
        contentEl.classList.remove('hidden');
    }
    
    // Add active class to selected button
    const btn = document.getElementById('tab-' + tabName);
    if (btn) {
        btn.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-purple-600', 'text-white', 'shadow-lg');
        btn.classList.remove('text-gray-700', 'hover:bg-gray-100');
    }
    
    // Load stats when danger tab is opened
    if (tabName === 'danger') {
        loadCurrentStats();
    }
}

function copyWebhook() {
    const webhook = 'https://reklama.kosonsoyliklar.uz/telegram/webhook.php';
    navigator.clipboard.writeText(webhook).then(() => {
        showToast('Webhook URL nusxa olindi!', 'success');
    });
}

// SYNC FUNCTIONS
async function checkSync() {
    try {
        showLoading();
        const response = await fetch('../../backend/packages/check.php');
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            displaySyncResults(result.data, false);
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
}

async function runSync() {
    if (!confirm('Barcha paketlarni bookinglar bilan sinxronlashni xohlaysizmi?')) {
        return;
    }
    
    try {
        showLoading();
        const response = await fetch('../../backend/packages/sync.php');
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            showToast(result.message, 'success');
            displaySyncResults(result.data, true);
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
}

function displaySyncResults(data, isFixed) {
    const container = document.getElementById('syncResults');
    container.classList.remove('hidden');
    
    if (isFixed) {
        if (data.fixed_count === 0) {
            container.innerHTML = `
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6 text-center">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                    <p class="text-green-800 font-bold text-lg">Barcha paketlar to'g'ri!</p>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 mb-4">
                    <p class="text-yellow-800 font-bold"><i class="fas fa-wrench mr-2"></i>${data.fixed_count} ta paket tuzatildi</p>
                </div>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    ${data.issues.map(issue => `
                        <div class="bg-white rounded-xl p-4 border flex items-center justify-between">
                            <div>
                                <p class="font-bold">${issue.customer}</p>
                                <p class="text-sm text-gray-600">${issue.total} ta reklama</p>
                            </div>
                            <div class="text-right">
                                <p class="text-red-500 line-through text-sm">${issue.was.used}/${issue.total}</p>
                                <p class="text-green-600 font-bold">${issue.now.used}/${issue.total} ✓</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
    } else {
        if (data.issues_count === 0) {
            container.innerHTML = `
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-6 text-center">
                    <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                    <p class="text-green-800 font-bold text-lg">Hammasi joyida! ✅</p>
                    <p class="text-green-600 text-sm">Barcha ${data.total_packages} ta paket to'g'ri</p>
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-4">
                    <p class="text-red-800 font-bold"><i class="fas fa-exclamation-triangle mr-2"></i>${data.issues_count} ta paketda muammo!</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm bg-white rounded-xl overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-3 text-left">Mijoz</th>
                                <th class="p-3 text-center">Jami</th>
                                <th class="p-3 text-center">Yozilgan</th>
                                <th class="p-3 text-center">Haqiqiy</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.issues.map(issue => `
                                <tr class="border-b">
                                    <td class="p-3"><p class="font-semibold">${issue.customer_name}</p></td>
                                    <td class="p-3 text-center">${issue.total_ads}</td>
                                    <td class="p-3 text-center text-red-600">${issue.recorded_used}</td>
                                    <td class="p-3 text-center text-green-600">${issue.actual_used}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <button onclick="runSync()" class="px-8 py-3 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-xl font-bold">
                        <i class="fas fa-wrench mr-2"></i>Barchasini tuzatish
                    </button>
                </div>
            `;
        }
    }
}

// DANGER ZONE FUNCTIONS
async function loadCurrentStats() {
    try {
        const response = await fetch('../../backend/system/get_stats.php');
        const result = await response.json();
        
        if (result.success) {
            document.getElementById('statCustomers').textContent = result.data.customers;
            document.getElementById('statPackages').textContent = result.data.customer_packages;
            document.getElementById('statBookings').textContent = result.data.bookings;
            document.getElementById('statPayments').textContent = result.data.payments;
        }
    } catch (error) {
        console.error('Stats error:', error);
    }
}

function confirmDelete(action) {
    const texts = {
        'bookings': 'Barcha BOOKINGLAR o\'chiriladi. Bu amalni qaytarib bo\'lmaydi!',
        'packages': 'Barcha PAKETLAR va TO\'LOVLAR o\'chiriladi. Bu amalni qaytarib bo\'lmaydi!',
        'templates': 'Barcha SHABLON PAKETLAR (packages jadvali) o\'chiriladi.',
        'all': '⚠️ BARCHA MA\'LUMOTLAR o\'chiriladi: Mijozlar, Paketlar, Bookinglar, To\'lovlar - HAMMASI!'
    };
    
    document.getElementById('deleteConfirmText').textContent = texts[action];
    document.getElementById('deleteAction').value = action;
    document.getElementById('confirmPassword').value = '';
    document.getElementById('deleteConfirmModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.add('hidden');
}

async function executeDelete() {
    const action = document.getElementById('deleteAction').value;
    const password = document.getElementById('confirmPassword').value;
    
    if (!password) {
        showToast('Parolni kiriting', 'error');
        return;
    }
    
    try {
        showLoading();
        
        const formData = new FormData();
        formData.append('action', action);
        formData.append('password', password);
        
        const response = await fetch('../../backend/system/reset_data.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            closeDeleteModal();
            showToast(result.message, 'success');
            loadCurrentStats();
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
}
</script>

</body>
</html>