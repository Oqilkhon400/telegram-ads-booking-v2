<?php
/**
 * Calendar Page
 * Kalendar va Booking sahifasi - YANGI LAYOUT
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

$page_title = 'Kalendar';
$page_script = 'calendar.js';

// Header
include '../components/header.php';
?>

<!-- Main Container -->
<div class="flex">
    
    <!-- Sidebar -->
    <?php include '../components/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="flex-1 lg:ml-64 mt-16 p-6">
        
        <!-- Page Header - CHIROYLI DIZAYN -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    <i class="fas fa-calendar-alt mr-3"></i>Kalendar
                </h2>
                <p class="text-gray-600 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    Reklama vaqtlarini bron qilish
                </p>
            </div>
            <button onclick="openAddBookingModal()" class="group relative bg-gradient-to-r from-blue-500 to-purple-600 text-white px-8 py-4 rounded-xl font-bold hover:from-blue-600 hover:to-purple-700 transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-2xl border-2 border-blue-400">
                <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 rounded-xl transition-opacity"></span>
                <i class="fas fa-plus-circle mr-2 text-xl"></i>Booking Qilish
            </button>
        </div>
        
        <!-- Top Section: Date Title + Search + Stats -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
                
                <!-- Left: Date Title - YASHIL RANG -->
                <div>
                    <h3 class="text-2xl font-bold text-green-600 flex items-center gap-2" id="dateTitle">
                        <i class="fas fa-calendar-alt"></i>
                        Yuklanmoqda...
                    </h3>
                    <p class="text-gray-600 text-sm mt-1 ml-8" id="dateSubtitle"></p>
                </div>
                
                <!-- Center: Search -->
                <div>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="calendarSearch" 
                            placeholder="Mijoz yoki reklama izlash..."
                            class="w-full px-4 py-2.5 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onkeyup="searchBookings()"
                        >
                        <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Right: Stats - GRADIENT CARDS -->
                <div class="flex items-center justify-end space-x-3">
                    <div class="text-center px-4 py-3 bg-gradient-to-br from-green-400 to-green-600 text-white rounded-xl shadow-lg transform hover:scale-105 transition">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-check-circle text-xl"></i>
                            <p class="text-xs font-semibold">Bo'sh</p>
                        </div>
                        <p class="text-2xl font-bold" id="availableCount">0</p>
                    </div>
                    <div class="text-center px-4 py-3 bg-gradient-to-br from-blue-400 to-blue-600 text-white rounded-xl shadow-lg transform hover:scale-105 transition">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-calendar-check text-xl"></i>
                            <p class="text-xs font-semibold">Band</p>
                        </div>
                        <p class="text-2xl font-bold" id="bookedCount">0</p>
                    </div>
                    <div class="text-center px-4 py-3 bg-gradient-to-br from-gray-400 to-gray-600 text-white rounded-xl shadow-lg transform hover:scale-105 transition">
                        <div class="flex items-center justify-center gap-2 mb-1">
                            <i class="fas fa-history text-xl"></i>
                            <p class="text-xs font-semibold">O'tgan</p>
                        </div>
                        <p class="text-2xl font-bold" id="pastCount">0</p>
                    </div>
                </div>
                
            </div>
        </div>
        
        <!-- Time Slots Grid - CHIROYLI CONTAINER -->
        <div class="bg-gradient-to-br from-white via-blue-50 to-purple-50 rounded-xl shadow-xl p-6 mb-6 border-2 border-blue-200">
            <div id="timeSlotsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                
                <!-- Loading -->
                <div class="col-span-full text-center py-12">
                    <div class="relative inline-block">
                        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600 mx-auto mb-4"></div>
                        <i class="fas fa-calendar-alt absolute top-5 left-5 text-2xl text-blue-600"></i>
                    </div>
                    <p class="text-gray-600 font-semibold">Yuklanmoqda...</p>
                </div>
                
            </div>
        </div>
        
        <!-- Bottom Navigation: Date Selector - CHIROYLI DIZAYN -->
        <div class="bg-gradient-to-r from-blue-50 via-purple-50 to-pink-50 rounded-xl shadow-lg p-6 border-2 border-blue-200">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                
                <!-- Previous Day -->
                <button onclick="changeDate(-1)" class="group p-4 bg-white hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-600 rounded-xl transition-all duration-300 border-2 border-blue-300 hover:border-transparent shadow-md hover:shadow-xl transform hover:scale-110">
                    <i class="fas fa-chevron-left text-blue-600 group-hover:text-white text-xl transition-colors"></i>
                </button>
                
                <!-- Date Input -->
                <input 
                    type="date" 
                    id="selectedDate" 
                    value="<?php echo date('Y-m-d'); ?>"
                    onchange="loadCalendar()"
                    class="px-8 py-4 text-lg font-bold border-3 border-blue-400 rounded-xl focus:ring-4 focus:ring-blue-300 focus:border-blue-600 shadow-lg bg-white hover:shadow-xl transition-all"
                >
                
                <!-- Next Day -->
                <button onclick="changeDate(1)" class="group p-4 bg-white hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-600 rounded-xl transition-all duration-300 border-2 border-blue-300 hover:border-transparent shadow-md hover:shadow-xl transform hover:scale-110">
                    <i class="fas fa-chevron-right text-blue-600 group-hover:text-white text-xl transition-colors"></i>
                </button>
                
                <!-- Today Button -->
                <button onclick="setToday()" class="px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 font-bold shadow-lg hover:shadow-xl transform hover:scale-105 border-2 border-blue-400">
                    <i class="fas fa-calendar-day mr-2"></i>Bugun
                </button>
                
            </div>
        </div>
        
    </main>
    
</div>

<!-- Add Booking Modal -->
<div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold">
                <i class="fas fa-calendar-plus mr-2"></i>Reklama Booking Qilish
            </h3>
        </div>
        
        <!-- Modal Body -->
        <form id="bookingForm" class="p-6 space-y-4">
            
            <!-- Mijoz qidirish -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-user mr-1 text-blue-500"></i>Mijoz <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="customerSearch" 
                        placeholder="Mijoz ismi yoki telefon raqamini yozing..."
                        autocomplete="off"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                    <input type="hidden" id="bookingCustomer" name="customer_id" required>
                    
                    <!-- Qidiruv natijalari -->
                    <div id="customerResults" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-gray-200 rounded-xl shadow-2xl max-h-64 overflow-y-auto">
                        <!-- JavaScript bilan to'ldiriladi -->
                    </div>
                </div>
                <!-- Tanlangan mijoz -->
                <div id="selectedCustomerInfo" class="hidden mt-2 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-blue-800" id="selectedCustomerName"></span>
                            <span class="text-sm text-blue-600 ml-2" id="selectedCustomerPhone"></span>
                        </div>
                        <button type="button" onclick="clearCustomerSelection()" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Paket -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-box mr-1 text-green-500"></i>Paket <span class="text-red-500">*</span>
                </label>
                <select 
                    id="bookingPackage" 
                    name="customer_package_id" 
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                    <option value="">Avval mijozni tanlang...</option>
                </select>
                <p class="text-xs text-gray-500 mt-1" id="packageInfo"></p>
            </div>
            
            <!-- Sana va Vaqt -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-1 text-orange-500"></i>Sana <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="bookingDate" 
                        name="slot_date" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-clock mr-1 text-purple-500"></i>Vaqt <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="bookingTime" 
                        name="slot_time" 
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    >
                        <option value="">Vaqtni tanlang...</option>
                        <?php for($h = 7; $h <= 22; $h++): ?>
                        <option value="<?php echo sprintf('%02d:00', $h); ?>"><?php echo sprintf('%02d:00', $h); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <!-- Izoh (ixtiyoriy) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-comment mr-1 text-gray-500"></i>Izoh <span class="text-gray-400">(ixtiyoriy)</span>
                </label>
                <textarea 
                    id="bookingNotes" 
                    name="ad_description" 
                    rows="2"
                    placeholder="Masalan: Komentariysiz chiqsin, Video format..."
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-none"
                ></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeBookingModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:from-blue-600 hover:to-purple-700 transition font-semibold shadow-lg">
                    <i class="fas fa-check mr-2"></i>Saqlash
                </button>
            </div>
            
        </form>
        
    </div>
</div>


<!-- Booking Details Modal -->
<div id="bookingDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 class="text-xl font-bold">Booking tafsilotlari</h3>
            <button onclick="closeBookingDetailsModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div id="bookingDetailsContent" class="p-6">
            <!-- JavaScript bilan to'ldiriladi -->
        </div>
        
    </div>
</div>

<!-- Move Booking Modal -->
<div id="moveBookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white px-6 py-4 rounded-t-2xl">
            <h3 class="text-xl font-bold">Booking vaqtini o'zgartirish</h3>
        </div>
        
        <!-- Modal Body -->
        <form id="moveBookingForm" class="p-6 space-y-4">
            <input type="hidden" id="moveBookingId" name="booking_id">
            
            <!-- Yangi sana -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Yangi sana <span class="text-red-500">*</span>
                </label>
                <input 
                    type="date" 
                    id="moveBookingDate" 
                    name="new_slot_date" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                >
            </div>
            
            <!-- Yangi vaqt -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Yangi vaqt <span class="text-red-500">*</span>
                </label>
                <select 
                    id="moveBookingTime" 
                    name="new_slot_time" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                >
                    <option value="">Vaqtni tanlang...</option>
                    <?php for($h = 7; $h <= 22; $h++): ?>
                    <option value="<?php echo sprintf('%02d:00', $h); ?>"><?php echo sprintf('%02d:00', $h); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeMoveBookingModal()" class="flex-1 px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition font-semibold">
                    Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 text-white rounded-lg hover:from-orange-600 hover:to-red-700 transition font-semibold">
                    <i class="fas fa-arrows-alt mr-2"></i>Ko'chirish
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<!-- Quick Add Customer Modal -->
<div id="quickCustomerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 class="text-xl font-bold"><i class="fas fa-user-plus mr-2"></i>Yangi mijoz</h3>
            <button type="button" onclick="closeQuickCustomerModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
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
                    placeholder="Masalan: Urgaz Gilamlari"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                >
            </div>
            
            <!-- Mas'ul shaxs -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mas'ul shaxs <span class="text-gray-400">(ixtiyoriy)</span>
                </label>
                <input 
                    type="text" 
                    id="quickCustomerContact" 
                    placeholder="Masalan: Jahongir"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                >
            </div>
            
            <!-- Telefon -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Telefon <span class="text-red-500">*</span>
                </label>
                <input 
                    type="tel" 
                    id="quickCustomerPhone" 
                    placeholder="+998901234567"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                >
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeQuickCustomerModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition font-semibold">
                    Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-xl hover:from-green-600 hover:to-teal-700 transition font-semibold">
                    <i class="fas fa-check mr-2"></i>Qo'shish
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<!-- Quick Add Package Modal -->
<div id="quickPackageModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-[60] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-green-500 to-teal-600 text-white px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 class="text-xl font-bold"><i class="fas fa-box mr-2"></i>Yangi Paket va To'lov</h3>
            <button type="button" onclick="closeQuickPackageModal()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <form id="quickPackageForm" class="p-6 space-y-5">
            <input type="hidden" id="quickPackageCustomerId">
            
            <!-- Mijoz info -->
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Mijoz</p>
                        <p class="font-bold text-blue-800" id="quickPackageCustomerName">-</p>
                    </div>
                </div>
            </div>
            
            <!-- Paket ma'lumotlari -->
            <div class="bg-gray-50 rounded-xl p-4 border">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-box text-green-500 mr-2"></i>Paket ma'lumotlari
                </h4>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Reklama soni <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="quickPackageAdsCount" 
                        min="1"
                        placeholder="Masalan: 7, 15, 30..."
                        required
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                </div>
            </div>
            
            <!-- To'lov ma'lumotlari -->
            <div class="bg-gray-50 rounded-xl p-4 border">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-credit-card text-purple-500 mr-2"></i>To'lov ma'lumotlari
                </h4>
                
                <!-- To'lov turi -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        To'lov turi <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="quickPackagePaymentType" 
                        required
                        onchange="togglePaymentAmount()"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="naqd">💵 Naqd</option>
                        <option value="karta">💳 Karta</option>
                        <option value="nasiya">📝 Nasiya</option>
                        <option value="bepul">🎁 Bepul</option>
                    </select>
                </div>
                
                <!-- To'lov summasi -->
                <div id="paymentAmountContainer">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        To'lov summasi (so'm) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="quickPackagePaymentAmount" 
                        min="0"
                        placeholder="Masalan: 100000"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                </div>
            </div>
            
            <!-- Izoh -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-comment text-gray-400 mr-1"></i>Izoh <span class="text-gray-400">(ixtiyoriy)</span>
                </label>
                <textarea 
                    id="quickPackageNotes" 
                    rows="2"
                    placeholder="Qo'shimcha ma'lumotlar..."
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                ></textarea>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeQuickPackageModal()" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition font-semibold">
                    Bekor qilish
                </button>
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-xl hover:from-green-600 hover:to-teal-700 transition font-semibold">
                    <i class="fas fa-check mr-2"></i>Saqlash
                </button>
            </div>
            
        </form>
        
    </div>
</div>

<?php include '../components/footer.php'; ?>