<?php
/**
 * TIZIMNI TEKSHIRISH SKRIPTI
 * System Health Check
 * 
 * Bu faylni public_html papkasiga joylashtiring
 * Brauzerda oching: https://reklama.kosonsoyliklar.uz/check-system.php
 */

// Xavfsizlik: Faqat localhost dan kirish mumkin (production da)
$allowed_ips = ['127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips) && !isset($_GET['allow'])) {
    // Production da bu qatorni uncommnet qiling:
    // die('Access denied. Bu faqat localhost dan ishlaydi.');
}

?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizim Tekshiruvi - Kosonsoy Reklama</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .check-item {
            background: #f8f9fa;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            border-left: 5px solid #ddd;
        }
        .check-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .check-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .check-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .check-title {
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check-desc {
            color: #666;
            font-size: 0.9rem;
        }
        .icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .icon.success { background: #28a745; }
        .icon.warning { background: #ffc107; }
        .icon.error { background: #dc3545; }
        .section {
            margin: 40px 0;
        }
        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        code {
            background: #f4f4f4;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e0e0e0;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Tizim Tekshiruvi</h1>
        <p class="subtitle">Kosonsoy Reklama Tizimi - System Health Check</p>

        <?php
        $checks = [];
        $total_checks = 0;
        $passed_checks = 0;
        $warning_checks = 0;
        $failed_checks = 0;

        // ========================================
        // PHP VERSION CHECK
        // ========================================
        $total_checks++;
        $php_version = phpversion();
        if (version_compare($php_version, '7.4.0', '>=')) {
            $checks[] = [
                'status' => 'success',
                'title' => 'PHP Versiya',
                'desc' => "PHP $php_version - To'g'ri ishlamoqda ✓"
            ];
            $passed_checks++;
        } else {
            $checks[] = [
                'status' => 'error',
                'title' => 'PHP Versiya',
                'desc' => "PHP $php_version - Kamida PHP 7.4 kerak!"
            ];
            $failed_checks++;
        }

        // ========================================
        // DATABASE CONNECTION CHECK
        // ========================================
        $total_checks++;
        if (file_exists('config/database.php')) {
            require_once 'config/database.php';
            
            if (isset($conn) && $conn->ping()) {
                $checks[] = [
                    'status' => 'success',
                    'title' => 'Database Ulanish',
                    'desc' => 'Database muvaffaqiyatli ulandi ✓'
                ];
                $passed_checks++;
                
                // Check tables
                $total_checks++;
                $tables = ['users', 'customers', 'packages', 'customer_packages', 'bookings', 'time_slots', 'payments'];
                $missing_tables = [];
                
                foreach ($tables as $table) {
                    $result = $conn->query("SHOW TABLES LIKE '$table'");
                    if ($result->num_rows == 0) {
                        $missing_tables[] = $table;
                    }
                }
                
                if (empty($missing_tables)) {
                    $checks[] = [
                        'status' => 'success',
                        'title' => 'Database Jadvallar',
                        'desc' => 'Barcha jadvallar mavjud (' . count($tables) . ' ta jadval) ✓'
                    ];
                    $passed_checks++;
                } else {
                    $checks[] = [
                        'status' => 'error',
                        'title' => 'Database Jadvallar',
                        'desc' => 'Yo\'qolgan jadvallar: ' . implode(', ', $missing_tables)
                    ];
                    $failed_checks++;
                }
                
            } else {
                $checks[] = [
                    'status' => 'error',
                    'title' => 'Database Ulanish',
                    'desc' => 'Database bilan bog\'lanib bo\'lmadi! config/database.php ni tekshiring.'
                ];
                $failed_checks++;
            }
        } else {
            $checks[] = [
                'status' => 'error',
                'title' => 'Database Konfiguratsiya',
                'desc' => 'config/database.php fayl topilmadi!'
            ];
            $failed_checks++;
        }

        // ========================================
        // REQUIRED PHP EXTENSIONS
        // ========================================
        $required_extensions = ['mysqli', 'json', 'mbstring', 'curl'];
        foreach ($required_extensions as $ext) {
            $total_checks++;
            if (extension_loaded($ext)) {
                $checks[] = [
                    'status' => 'success',
                    'title' => "PHP Extension: $ext",
                    'desc' => "$ext extension yoqilgan ✓"
                ];
                $passed_checks++;
            } else {
                $checks[] = [
                    'status' => 'error',
                    'title' => "PHP Extension: $ext",
                    'desc' => "$ext extension yo'q! Uni yoqish kerak."
                ];
                $failed_checks++;
            }
        }

        // ========================================
        // FILE PERMISSIONS
        // ========================================
        $folders_to_check = ['assets', 'backend', 'config', 'frontend', 'telegram'];
        foreach ($folders_to_check as $folder) {
            $total_checks++;
            if (is_dir($folder)) {
                if (is_readable($folder) && is_writable($folder)) {
                    $checks[] = [
                        'status' => 'success',
                        'title' => "Papka: $folder",
                        'desc' => "Ruxsatlar to'g'ri (readable & writable) ✓"
                    ];
                    $passed_checks++;
                } else {
                    $checks[] = [
                        'status' => 'warning',
                        'title' => "Papka: $folder",
                        'desc' => "Ruxsatlar noto'g'ri bo'lishi mumkin. chmod 755 qiling."
                    ];
                    $warning_checks++;
                }
            } else {
                $checks[] = [
                    'status' => 'error',
                    'title' => "Papka: $folder",
                    'desc' => "Papka topilmadi!"
                ];
                $failed_checks++;
            }
        }

        // ========================================
        // IMPORTANT FILES
        // ========================================
        $important_files = [
            'index.php' => 'Bosh sahifa',
            '.htaccess' => 'Apache sozlamalari',
            'config/database.php' => 'Database konfiguratsiya',
            'frontend/login.php' => 'Login sahifa',
            'telegram/bot.php' => 'Telegram bot'
        ];
        
        foreach ($important_files as $file => $desc) {
            $total_checks++;
            if (file_exists($file)) {
                $checks[] = [
                    'status' => 'success',
                    'title' => $desc,
                    'desc' => "<code>$file</code> mavjud ✓"
                ];
                $passed_checks++;
            } else {
                $checks[] = [
                    'status' => 'error',
                    'title' => $desc,
                    'desc' => "<code>$file</code> topilmadi!"
                ];
                $failed_checks++;
            }
        }

        // ========================================
        // TELEGRAM BOT CHECK
        // ========================================
        $total_checks++;
        if (file_exists('telegram/bot.php')) {
            $bot_content = file_get_contents('telegram/bot.php');
            if (strpos($bot_content, 'YOUR_BOT_TOKEN_HERE') !== false) {
                $checks[] = [
                    'status' => 'warning',
                    'title' => 'Telegram Bot Token',
                    'desc' => 'Bot token hali o\'rnatilmagan! telegram/bot.php faylda BOT_TOKEN ni o\'rnating.'
                ];
                $warning_checks++;
            } else {
                $checks[] = [
                    'status' => 'success',
                    'title' => 'Telegram Bot Token',
                    'desc' => 'Bot token o\'rnatilgan ✓'
                ];
                $passed_checks++;
            }
        }

        // ========================================
        // DISPLAY RESULTS
        // ========================================
        ?>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_checks; ?></div>
                <div class="stat-label">Jami Tekshiruvlar</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #28a745;"><?php echo $passed_checks; ?></div>
                <div class="stat-label">✓ Muvaffaqiyatli</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #ffc107;"><?php echo $warning_checks; ?></div>
                <div class="stat-label">⚠ Ogohlantirish</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?php echo $failed_checks; ?></div>
                <div class="stat-label">✗ Xatolik</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">📋 Tekshiruv Natijalari</div>
            <?php foreach ($checks as $check): ?>
                <div class="check-item <?php echo $check['status']; ?>">
                    <div class="check-title">
                        <span class="icon <?php echo $check['status']; ?>">
                            <?php 
                            echo $check['status'] == 'success' ? '✓' : 
                                 ($check['status'] == 'warning' ? '⚠' : '✗'); 
                            ?>
                        </span>
                        <?php echo $check['title']; ?>
                    </div>
                    <div class="check-desc"><?php echo $check['desc']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($failed_checks > 0): ?>
            <div class="section">
                <div class="section-title">🔧 Qanday Tuzatish?</div>
                <div class="check-item error">
                    <div class="check-title">Xatoliklarni Tuzatish</div>
                    <div class="check-desc">
                        <p>Agar xatoliklar ko'rsatilgan bo'lsa:</p>
                        <ol style="margin-top: 10px; padding-left: 20px;">
                            <li>Database sozlamalarini tekshiring (<code>config/database.php</code>)</li>
                            <li>Barcha fayllar to'liq yuklangan bo'lsin</li>
                            <li>SQL faylni import qilishni unutmang</li>
                            <li>Folder ruxsatlarini to'g'rilang (chmod 755)</li>
                            <li>PHP extensionlarni yoqing</li>
                        </ol>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($failed_checks == 0 && $warning_checks == 0): ?>
            <div class="section">
                <div class="check-item success">
                    <div class="check-title">
                        <span class="icon success">✓</span>
                        🎉 Ajoyib! Tizim To'liq Tayyor
                    </div>
                    <div class="check-desc">
                        Barcha tekshiruvlardan o'tdingiz. Tizim ishga tushishga tayyor!
                        <br><br>
                        <strong>Keyingi qadamlar:</strong>
                        <ul style="margin-top: 10px; padding-left: 20px;">
                            <li>Admin panelga kiring: <a href="frontend/login.php">frontend/login.php</a></li>
                            <li>Bosh sahifani ko'ring: <a href="index.php">index.php</a></li>
                            <li>Telegram botni sozlang</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="section">
            <div class="section-title">ℹ️ Tizim Ma'lumotlari</div>
            <div class="check-item">
                <div class="check-desc">
                    <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?><br>
                    <strong>PHP Versiya:</strong> <?php echo PHP_VERSION; ?><br>
                    <strong>Server OS:</strong> <?php echo PHP_OS; ?><br>
                    <strong>Root Path:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
                    <strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
        </div>

        <p style="text-align: center; color: #666; margin-top: 40px;">
            © 2025 Kosonsoy Reklama Tizimi | System Check v1.0
        </p>
    </div>
</body>
</html>
