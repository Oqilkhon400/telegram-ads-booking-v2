<?php
/**
 * Packages Page - GROUPED BY CUSTOMER
 * Paketlar sahifasi - Mijozlar bo'yicha guruhlangan
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

$page_title = 'Paketlar';

// Header
include '../components/header.php';
?>

<!-- Main Container -->
<div class="flex">
    
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 mt-16 p-6">
        
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Paketlar</h2>
                <p class="text-gray-600">Mijozga paket biriktirish va boshqarish</p>
            </div>
            <button onclick="openNewPackageModal()" class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-green-600 hover:to-teal-700 transition transform hover:scale-105 shadow-lg">
                <i class="fas fa-plus mr-2"></i>Yangi Paket
            </button>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Mijoz ismi, telefon..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                    >
                </div>
                <select id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                    <option value="all">Barchasi</option>
                    <option value="active" selected>Faqat aktiv</option>
                    <option value="completed">Tugagan</option>
                </select>
                <button onclick="resetFilters()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
        
        <!-- Packages List (Grouped by Customer) -->
        <div class="space-y-4" id="packagesContainer">
            <!-- Loading -->
            <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-500 mx-auto mb-4"></div>
                <p class="text-gray-500">Yuklanmoqda...</p>
            </div>
        </div>
        
    </main>
    
</div>

<!-- New Package Modal -->
<div id="packageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold">Yangi Paket va To'lov</h3>
        </div>
        
        <!-- Modal Body -->
        <form id="packageForm" class="p-6 space-y-6">
            
            <!-- Mijoz tanlash -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <label class="block text-sm font-bold text-gray-800 mb-3">
                    1️⃣ Mijozni tanlang <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-3">
                    <select 
                        id="selectedCustomer" 
                        name="customer_id"
                        required
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg"
                    >
                        <option value="">Mijozni tanlang...</option>
                    </select>
                    <button type="button" onclick="openQuickAddCustomer()" class="px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition whitespace-nowrap">
                        <i class="fas fa-user-plus mr-2"></i>Yangi
                    </button>
                </div>
            </div>
            
            <!-- Paket ma'lumotlari -->
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                <label class="block text-sm font-bold text-gray-800 mb-3">
                    2️⃣ Paket ma'lumotlari
                </label>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reklama soni <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="packageTotalAds" 
                        name="total_ads" 
                        required
                        min="1"
                        max="100"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-lg"
                        placeholder="7, 15, 30..."
                    >
                </div>
            </div>
            
            <!-- To'lov ma'lumotlari -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <label class="block text-sm font-bold text-gray-800 mb-3">
                    3️⃣ To'lov ma'lumotlari
                </label>
                
                <div class="space-y-4">
                    <!-- To'lov turi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            To'lov turi <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="paymentMethod" 
                            name="payment_method"
                            required
                            onchange="togglePaymentAmount()"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg"
                        >
                            <option value="naqd">💵 Naqd</option>
                            <option value="karta">💳 Karta</option>
                            <option value="nasiya">📅 Nasiya</option>
                            <option value="bepul">🎁 Bepul</option>
                        </select>
                    </div>
                    
                    <!-- To'lov summasi -->
                    <div id="paymentAmountField">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            To'lov summasi (so'm) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="packagePrice" 
                            name="price" 
                            min="0"
                            step="1000"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-lg"
                            placeholder="To'lov summasini kiriting"
                        >
                    </div>
                    
                    <!-- Izoh -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Izoh</label>
                        <textarea 
                            id="paymentNotes" 
                            name="notes" 
                            rows="2"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Qo'shimcha ma'lumotlar..."
                        ></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closePackageModal()" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                    Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-lg hover:from-green-600 hover:to-teal-700 transition font-semibold">
                    <i class="fas fa-check mr-2"></i>Saqlash
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<!-- Edit Package Modal -->
<div id="editPackageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold">Paketni Tahrirlash</h3>
        </div>
        
        <form id="editPackageForm" class="p-6 space-y-4">
            <input type="hidden" id="editPackageId">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reklama soni <span class="text-red-500">*</span>
                </label>
                <input 
                    type="number" 
                    id="editTotalAds" 
                    required
                    min="1"
                    max="100"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg"
                >
                <p class="text-xs text-gray-500 mt-1">Jami reklama sonini o'zgartiring</p>
            </div>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 text-sm">
                <p class="text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Diqqat: Ishlatilgan reklamalar soni (<span id="editUsedAds">0</span>) dan kam qiymat kiritish mumkin emas!
                </p>
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeEditModal()" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                    Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>Saqlash
                </button>
            </div>
        </form>
        
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div id="quickCustomerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold">Tezkor mijoz qo'shish</h3>
        </div>
        
        <form id="quickCustomerForm" class="p-6 space-y-4">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reklama nomi <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="quickCustomerName" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Kosonsoyliklar, iPost..."
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Telefon raqam <span class="text-red-500">*</span>
                </label>
                <input 
                    type="tel" 
                    id="quickCustomerPhone" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="+998901234567"
                >
            </div>
            
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeQuickCustomerModal()" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                    Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>Saqlash
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<script src="../../assets/js/packages-grouped.js"></script>

<?php include '../components/footer.php'; ?>