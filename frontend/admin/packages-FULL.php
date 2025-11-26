<?php
/**
 * Packages Page - FULL VERSION
 * Paketlar sahifasi - To'liq versiya
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
        
        <!-- Active Packages List -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-6 py-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-box mr-2"></i>
                        Aktiv Paketlar
                    </h3>
                    <span id="totalPackagesCount" class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm">0 ta</span>
                </div>
            </div>
            
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Mijoz</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Paket</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Qoldiq</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">To'lov</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-700">Sana</th>
                            <th class="text-right py-3 px-4 font-semibold text-gray-700">Amal</th>
                        </tr>
                    </thead>
                    <tbody id="activePackagesTable">
                        <tr>
                            <td colspan="6" class="text-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-500 mx-auto mb-4"></div>
                                <p class="text-gray-500">Yuklanmoqda...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
                        <i class="fas fa-user-plus mr-2"></i>Yangi mijoz
                    </button>
                </div>
            </div>
            
            <!-- Paket ma'lumotlari -->
            <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                <label class="block text-sm font-bold text-gray-800 mb-3">
                    2️⃣ Paket ma'lumotlari
                </label>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Reklama soni -->
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
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg"
                            placeholder="7, 15, 30..."
                        >
                        <p class="text-xs text-gray-500 mt-1">Nechta reklama chiqarish mumkin</p>
                    </div>
                    
                    <!-- Narx -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Narx (so'm) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="packagePrice" 
                            name="price" 
                            required
                            min="0"
                            step="1000"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg"
                            placeholder="0 = Bepul"
                        >
                        <p class="text-xs text-gray-500 mt-1">0 kiritsangiz bepul</p>
                    </div>
                </div>
            </div>
            
            <!-- To'lov ma'lumotlari -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <label class="block text-sm font-bold text-gray-800 mb-3">
                    3️⃣ To'lov ma'lumotlari
                </label>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- To'lov turi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            To'lov turi <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="paymentMethod" 
                            name="payment_method"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg"
                        >
                            <option value="naqd">💵 Naqd</option>
                            <option value="karta">💳 Karta</option>
                            <option value="nasiya">📅 Nasiya</option>
                            <option value="bepul">🎁 Bepul</option>
                        </select>
                    </div>
                    
                    <!-- To'lov sanasi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            To'lov sanasi <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="paymentDate" 
                            name="payment_date" 
                            required
                            value="<?php echo date('Y-m-d'); ?>"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                    </div>
                </div>
                
                <!-- Izoh -->
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Izoh (ixtiyoriy)
                    </label>
                    <textarea 
                        id="paymentNotes" 
                        name="notes" 
                        rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Qo'shimcha ma'lumotlar..."
                    ></textarea>
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
                <button type="button" onclick="submitWithPayment()" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition font-semibold">
                    <i class="fas fa-save mr-2"></i>Bron qilish
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div id="quickCustomerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold">Tezkor mijoz qo'shish</h3>
        </div>
        
        <!-- Modal Body -->
        <form id="quickCustomerForm" class="p-6 space-y-4">
            
            <!-- Reklama nomi -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reklama nomi <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="quickCustomerName" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Kosonsoyliklar, iPost..."
                >
            </div>
            
            <!-- Telefon -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Telefon raqam <span class="text-red-500">*</span>
                </label>
                <input 
                    type="tel" 
                    id="quickCustomerPhone" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="+998901234567"
                >
            </div>
            
            <!-- Buttons -->
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

<script src="../../assets/js/packages.js"></script>

<?php include '../components/footer.php'; ?>
