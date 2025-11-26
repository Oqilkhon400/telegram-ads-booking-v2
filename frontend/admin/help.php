<?php
/**
 * Yordam Sahifasi
 * FAQ, Qo'llanma va Qo'llab-quvvatlash
 */

require_once '../../config/database.php';

// Session va autentifikatsiya
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yordam - Kosonsoy Reklama</title>
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
                    <i class="fas fa-question-circle text-green-600"></i>
                    Yordam va Qo'llab-quvvatlash
                </h1>
                <p class="text-gray-600">FAQ, qo'llanmalar va aloqa</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Quick Links -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <h2 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-link text-blue-600 mr-2"></i>
                            Tezkor Havolalar
                        </h2>
                        <nav class="space-y-2">
                            <a href="#faq" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-question-circle text-blue-600 w-5"></i>
                                <span class="font-medium text-gray-700">FAQ</span>
                            </a>
                            <a href="#guides" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-book text-purple-600 w-5"></i>
                                <span class="font-medium text-gray-700">Qo'llanmalar</span>
                            </a>
                            <a href="#video" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-video text-red-600 w-5"></i>
                                <span class="font-medium text-gray-700">Video Darslar</span>
                            </a>
                            <a href="#support" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 transition">
                                <i class="fas fa-headset text-green-600 w-5"></i>
                                <span class="font-medium text-gray-700">Qo'llab-quvvatlash</span>
                            </a>
                        </nav>
                    </div>

                    <!-- Contact Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold mb-1">Yordam Kerakmi?</h3>
                                <p class="text-sm opacity-90">24/7 Qo'llab-quvvatlash</p>
                            </div>
                            <i class="fas fa-headset text-4xl opacity-30"></i>
                        </div>
                        <a href="https://t.me/kosonsoy_admin" target="_blank" 
                           class="block w-full py-3 bg-white text-blue-600 text-center rounded-lg font-medium hover:shadow-lg transition mt-4">
                            <i class="fab fa-telegram mr-2"></i>Bog'lanish
                        </a>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- FAQ Section -->
                    <div id="faq" class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                            Tez-tez So'raladigan Savollar (FAQ)
                        </h2>

                        <div class="space-y-4">
                            <!-- FAQ Item 1 -->
                            <div class="border-b pb-4">
                                <button onclick="toggleFAQ(1)" class="w-full flex items-start justify-between text-left">
                                    <h3 class="font-semibold text-gray-800 pr-4">Yangi mijoz qanday qo'shiladi?</h3>
                                    <i id="faq-icon-1" class="fas fa-chevron-down text-gray-400 mt-1 transition-transform"></i>
                                </button>
                                <div id="faq-content-1" class="hidden mt-3 text-gray-600 text-sm">
                                    <ol class="list-decimal list-inside space-y-2">
                                        <li>Sidebar'dan "Mijozlar" sahifasiga o'ting</li>
                                        <li>"Yangi Mijoz" tugmasini bosing</li>
                                        <li>Mijoz ma'lumotlarini kiriting (ism, telefon)</li>
                                        <li>"Saqlash" tugmasini bosing</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="border-b pb-4">
                                <button onclick="toggleFAQ(2)" class="w-full flex items-start justify-between text-left">
                                    <h3 class="font-semibold text-gray-800 pr-4">Paket qanday tayinlanadi?</h3>
                                    <i id="faq-icon-2" class="fas fa-chevron-down text-gray-400 mt-1 transition-transform"></i>
                                </button>
                                <div id="faq-content-2" class="hidden mt-3 text-gray-600 text-sm">
                                    <ol class="list-decimal list-inside space-y-2">
                                        <li>"Paketlar" sahifasiga o'ting</li>
                                        <li>Paket yarating (agar yo'q bo'lsa)</li>
                                        <li>"Mijozga Biriktirish" tugmasini bosing</li>
                                        <li>Mijoz va to'lov ma'lumotlarini kiriting</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="border-b pb-4">
                                <button onclick="toggleFAQ(3)" class="w-full flex items-start justify-between text-left">
                                    <h3 class="font-semibold text-gray-800 pr-4">Reklama qanday bronlanadi?</h3>
                                    <i id="faq-icon-3" class="fas fa-chevron-down text-gray-400 mt-1 transition-transform"></i>
                                </button>
                                <div id="faq-content-3" class="hidden mt-3 text-gray-600 text-sm">
                                    <ol class="list-decimal list-inside space-y-2">
                                        <li>"Kalendar" sahifasiga o'ting</li>
                                        <li>Bo'sh vaqt slotini tanlang</li>
                                        <li>Mijoz va paketni tanlang</li>
                                        <li>Reklama matni va izohni kiriting</li>
                                        <li>"Bronlash" tugmasini bosing</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="border-b pb-4">
                                <button onclick="toggleFAQ(4)" class="w-full flex items-start justify-between text-left">
                                    <h3 class="font-semibold text-gray-800 pr-4">To'lovlar qanday qayd etiladi?</h3>
                                    <i id="faq-icon-4" class="fas fa-chevron-down text-gray-400 mt-1 transition-transform"></i>
                                </button>
                                <div id="faq-content-4" class="hidden mt-3 text-gray-600 text-sm">
                                    <p class="mb-2">To'lovlar avtomatik qayd etiladi paket tayinlanganda:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Paket tayinlash jarayonida to'lov summasi va usuli kiritiladi</li>
                                        <li>"To'lovlar" sahifasida barcha to'lovlarni ko'rish mumkin</li>
                                        <li>Export qilish va statistika olish mumkin</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- FAQ Item 5 -->
                            <div class="border-b pb-4">
                                <button onclick="toggleFAQ(5)" class="w-full flex items-start justify-between text-left">
                                    <h3 class="font-semibold text-gray-800 pr-4">Telegram bot qanday ishlaydi?</h3>
                                    <i id="faq-icon-5" class="fas fa-chevron-down text-gray-400 mt-1 transition-transform"></i>
                                </button>
                                <div id="faq-content-5" class="hidden mt-3 text-gray-600 text-sm">
                                    <p class="mb-2">Bot avtomatik xabarlar yuboradi:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>Reklama bronlanganda tasdiq xabari</li>
                                        <li>Reklama chiqishidan oldin eslatma</li>
                                        <li>Reklama chiqgandan keyin xabar</li>
                                        <li>Paket tugashidan oldin ogohlantirish</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- FAQ Item 6 -->
                            <div class="pb-4">
                                <button onclick="toggleFAQ(6)" class="w-full flex items-start justify-between text-left">
                                    <h3 class="font-semibold text-gray-800 pr-4">Statistikani qayerdan ko'rish mumkin?</h3>
                                    <i id="faq-icon-6" class="fas fa-chevron-down text-gray-400 mt-1 transition-transform"></i>
                                </button>
                                <div id="faq-content-6" class="hidden mt-3 text-gray-600 text-sm">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li><strong>Dashboard:</strong> Umumiy statistika va grafiklar</li>
                                        <li><strong>Statistika:</strong> Batafsil hisobotlar (faqat superadmin)</li>
                                        <li><strong>Mijozlar:</strong> Har bir mijoz statistikasi</li>
                                        <li><strong>To'lovlar:</strong> Moliyaviy hisobotlar</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guides Section -->
                    <div id="guides" class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-book text-purple-600 mr-2"></i>
                            Qo'llanmalar
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Guide 1 -->
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-user-plus text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 mb-1">Mijozlar Boshqaruvi</h3>
                                        <p class="text-sm text-gray-600">Mijoz qo'shish, tahrirlash va paket tayinlash</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Guide 2 -->
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-box text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 mb-1">Paketlar Yaratish</h3>
                                        <p class="text-sm text-gray-600">Yangi paket yaratish va narxlash</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Guide 3 -->
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-calendar text-green-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 mb-1">Kalendar Boshqaruvi</h3>
                                        <p class="text-sm text-gray-600">Vaqt slotlari va bronlash</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Guide 4 -->
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <div class="flex items-start space-x-3">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-money-bill text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 mb-1">To'lovlar</h3>
                                        <p class="text-sm text-gray-600">To'lov usullari va hisobotlar</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Tutorials -->
                    <div id="video" class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-video text-red-600 mr-2"></i>
                            Video Darslar
                        </h2>
                        
                        <div class="bg-gray-50 rounded-lg p-8 text-center">
                            <i class="fas fa-play-circle text-6xl text-gray-300 mb-4"></i>
                            <p class="text-gray-600 mb-4">Video darslar tez orada qo'shiladi</p>
                            <p class="text-sm text-gray-500">Telegram orqali bog'laning va batafsil ma'lumot oling</p>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div id="support" class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">
                            <i class="fas fa-headset text-green-600 mr-2"></i>
                            Qo'llab-quvvatlash
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Telegram -->
                            <a href="https://t.me/kosonsoy_admin" target="_blank" 
                               class="border-2 border-blue-200 rounded-lg p-6 text-center hover:border-blue-400 hover:shadow-md transition">
                                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fab fa-telegram text-3xl text-blue-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-2">Telegram</h3>
                                <p class="text-sm text-gray-600">@kosonsoy_admin</p>
                            </a>

                            <!-- Phone -->
                            <a href="tel:+998942777797" 
                               class="border-2 border-green-200 rounded-lg p-6 text-center hover:border-green-400 hover:shadow-md transition">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-phone text-3xl text-green-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-2">Telefon</h3>
                                <p class="text-sm text-gray-600">+998 94 277 77 97</p>
                            </a>

                            <!-- Email -->
                            <a href="mailto:info@kosonsoyliklar.uz" 
                               class="border-2 border-purple-200 rounded-lg p-6 text-center hover:border-purple-400 hover:shadow-md transition">
                                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-envelope text-3xl text-purple-600"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 mb-2">Email</h3>
                                <p class="text-sm text-gray-600">info@kosonsoyliklar.uz</p>
                            </a>
                        </div>

                        <!-- Working Hours -->
                        <div class="mt-6 bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-clock text-blue-600 text-2xl"></i>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Ish Vaqti</h3>
                                    <p class="text-sm text-gray-600">Dushanba - Yakshanba: 09:00 - 22:00</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tips -->
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-500 rounded-lg p-6">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-lightbulb text-yellow-600 text-2xl mt-1"></i>
                            <div>
                                <h3 class="font-semibold text-gray-800 mb-2">Maslahatlar</h3>
                                <ul class="text-sm text-gray-700 space-y-1">
                                    <li>• Parolni muntazam o'zgartiring</li>
                                    <li>• Mijoz ma'lumotlarini to'g'ri kiriting</li>
                                    <li>• Kalendarda vaqt slotlarini nazorat qiling</li>
                                    <li>• To'lovlarni darhol qayd eting</li>
                                    <li>• Statistikani kundalik kuzatib boring</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    <?php include '../components/footer.php'; ?>

    <script>
        function toggleFAQ(id) {
            const content = document.getElementById('faq-content-' + id);
            const icon = document.getElementById('faq-icon-' + id);
            
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>

</body>
</html>
