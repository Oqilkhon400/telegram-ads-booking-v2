<?php
/**
 * Sozlamalar Sahifasi
 * Profil, parol va tizim sozlamalari
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

// Foydalanuvchi ma'lumotlarini olish
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

// Profil yangilash
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = clean_input($_POST['full_name']);
    $username = clean_input($_POST['username']);
    
    // Username mavjudligini tekshirish
    $check_query = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
    $check_result = $conn->query($check_query);
    
    if ($check_result->num_rows > 0) {
        $error_message = "Bu username allaqachon band!";
    } else {
        $update_query = "UPDATE users SET full_name = '$full_name', username = '$username' WHERE id = $user_id";
        
        if ($conn->query($update_query)) {
            $_SESSION['full_name'] = $full_name;
            $success_message = "Profil muvaffaqiyatli yangilandi!";
            
            // Yangi ma'lumotlarni olish
            $user_result = $conn->query($user_query);
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
        $update_query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
        
        if ($conn->query($update_query)) {
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
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sozlamalar - Kosonsoy Reklama</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

    <?php include '../components/header.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="lg:ml-64 pt-16 min-h-screen">
        <div class="p-6">
            
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-cog text-blue-600"></i>
                    Sozlamalar
                </h1>
                <p class="text-gray-600">Profil va tizim sozlamalari</p>
            </div>

            <?php if ($success_message): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                    <p class="text-green-700 font-medium"><?php echo $success_message; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                    <p class="text-red-700 font-medium"><?php echo $error_message; ?></p>
                </div>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Sidebar Menu -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-4">
                        <nav class="space-y-2">
                            <button onclick="showTab('profile')" id="tab-profile" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                                <i class="fas fa-user w-5"></i>
                                <span class="font-medium">Profil</span>
                            </button>
                            <button onclick="showTab('password')" id="tab-password" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-lock w-5"></i>
                                <span class="font-medium">Parol</span>
                            </button>
                            <?php if ($user['role'] === 'superadmin'): ?>
                            <button onclick="showTab('system')" id="tab-system" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cogs w-5"></i>
                                <span class="font-medium">Tizim</span>
                            </button>
                            <button onclick="showTab('telegram')" id="tab-telegram" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition text-gray-700 hover:bg-gray-100">
                                <i class="fab fa-telegram w-5"></i>
                                <span class="font-medium">Telegram Bot</span>
                            </button>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="lg:col-span-2">
                    
                    <!-- Profile Tab -->
                    <div id="content-profile" class="tab-content">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6">
                                <i class="fas fa-user-edit text-blue-600 mr-2"></i>
                                Profil Ma'lumotlari
                            </h2>
                            
                            <form method="POST" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        To'liq Ism
                                    </label>
                                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" 
                                           required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Username
                                    </label>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                                           required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Rol
                                    </label>
                                    <input type="text" value="<?php echo $user['role'] === 'superadmin' ? 'Super Admin' : 'Admin'; ?>" 
                                           disabled class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Oxirgi Kirish
                                    </label>
                                    <input type="text" value="<?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Hech qachon'; ?>" 
                                           disabled class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                                </div>

                                <button type="submit" name="update_profile" class="w-full py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg font-medium hover:shadow-lg transition">
                                    <i class="fas fa-save mr-2"></i>Saqlash
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Password Tab -->
                    <div id="content-password" class="tab-content hidden">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6">
                                <i class="fas fa-key text-yellow-600 mr-2"></i>
                                Parolni O'zgartirish
                            </h2>
                            
                            <form method="POST" class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Joriy Parol
                                    </label>
                                    <input type="password" name="current_password" required 
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Yangi Parol
                                    </label>
                                    <input type="password" name="new_password" required minlength="6"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p class="text-sm text-gray-500 mt-1">Kamida 6 belgidan iborat bo'lishi kerak</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Parolni Tasdiqlang
                                    </label>
                                    <input type="password" name="confirm_password" required minlength="6"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <button type="submit" name="change_password" class="w-full py-3 bg-gradient-to-r from-yellow-500 to-orange-600 text-white rounded-lg font-medium hover:shadow-lg transition">
                                    <i class="fas fa-lock mr-2"></i>Parolni O'zgartirish
                                </button>
                            </form>
                        </div>
                    </div>

                    <?php if ($user['role'] === 'superadmin'): ?>
                    <!-- System Tab -->
                    <div id="content-system" class="tab-content hidden">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6">
                                <i class="fas fa-server text-purple-600 mr-2"></i>
                                Tizim Sozlamalari
                            </h2>
                            
                            <div class="space-y-6">
                                <!-- Database Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-3">Database Ma'lumotlari</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Host:</span>
                                            <span class="font-medium"><?php echo DB_HOST; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Database:</span>
                                            <span class="font-medium"><?php echo DB_NAME; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Ulanish:</span>
                                            <span class="text-green-600 font-medium">
                                                <i class="fas fa-check-circle mr-1"></i>Faol
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- PHP Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-3">Server Ma'lumotlari</h3>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">PHP Versiya:</span>
                                            <span class="font-medium"><?php echo PHP_VERSION; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Server:</span>
                                            <span class="font-medium"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Hozirgi vaqt:</span>
                                            <span class="font-medium"><?php echo date('d.m.Y H:i:s'); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Backup -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start space-x-3">
                                        <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                                        <div>
                                            <h4 class="font-semibold text-blue-900 mb-1">Backup Eslatma</h4>
                                            <p class="text-sm text-blue-800">Database backup'ni muntazam oling. cPanel orqali avtomatik backup sozlang.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Telegram Tab -->
                    <div id="content-telegram" class="tab-content hidden">
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-6">
                                <i class="fab fa-telegram text-blue-500 mr-2"></i>
                                Telegram Bot Sozlamalari
                            </h2>
                            
                            <div class="space-y-6">
                                <!-- Bot Status -->
                                <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h3 class="text-lg font-bold mb-1">Bot Holati</h3>
                                            <p class="text-sm opacity-90">Telegram bot integratsiya</p>
                                        </div>
                                        <i class="fab fa-telegram text-5xl opacity-30"></i>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                        <span class="font-medium">Faol</span>
                                    </div>
                                </div>

                                <!-- Bot Instructions -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-3">Bot Sozlash</h3>
                                    <ol class="list-decimal list-inside space-y-2 text-sm text-gray-600">
                                        <li>@BotFather ga murojaat qiling</li>
                                        <li>Bot token oling</li>
                                        <li><code class="bg-gray-200 px-2 py-1 rounded">telegram/bot.php</code> faylga token kiriting</li>
                                        <li>Webhook o'rnating</li>
                                        <li>Bot'ni test qiling</li>
                                    </ol>
                                </div>

                                <!-- Webhook Info -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h3 class="font-semibold text-gray-700 mb-3">Webhook URL</h3>
                                    <div class="bg-white rounded border p-3 flex items-center justify-between">
                                        <code class="text-sm text-gray-700">https://reklama.kosonsoyliklar.uz/telegram/webhook.php</code>
                                        <button onclick="copyWebhook()" class="text-blue-600 hover:text-blue-700">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Test Bot -->
                                <a href="https://t.me/kosonsoy_bot" target="_blank" 
                                   class="block w-full py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-center rounded-lg font-medium hover:shadow-lg transition">
                                    <i class="fab fa-telegram mr-2"></i>Bot'ni Ochish
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('[id^="tab-"]').forEach(btn => {
                btn.classList.remove('bg-gradient-to-r', 'from-blue-500', 'to-purple-600', 'text-white');
                btn.classList.add('text-gray-700', 'hover:bg-gray-100');
            });
            
            // Show selected tab
            document.getElementById('content-' + tabName).classList.remove('hidden');
            
            // Add active class to selected button
            const btn = document.getElementById('tab-' + tabName);
            btn.classList.add('bg-gradient-to-r', 'from-blue-500', 'to-purple-600', 'text-white');
            btn.classList.remove('text-gray-700', 'hover:bg-gray-100');
        }

        function copyWebhook() {
            const webhook = 'https://reklama.kosonsoyliklar.uz/telegram/webhook.php';
            navigator.clipboard.writeText(webhook).then(() => {
                alert('Webhook URL nusxa olindi!');
            });
        }
    </script>

</body>
</html>
