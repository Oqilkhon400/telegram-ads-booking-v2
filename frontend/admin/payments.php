<?php
/**
 * Payments Page - With Nasiya Tab
 * To'lovlar sahifasi - Nasiya tab bilan
 */

require_once '../../config/database.php';
require_once '../../config/settings.php';

$page_title = 'To\'lovlar';

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
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-800 mb-2">To'lovlar</h2>
            <p class="text-gray-600">To'lovlar tarixi va statistika</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            
            <!-- Bugungi to'lovlar -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-green-100 text-sm">Bugungi To'lovlar</p>
                    <i class="fas fa-calendar-day text-2xl opacity-50"></i>
                </div>
                <h3 class="text-2xl font-bold" id="todayAmount">0 so'm</h3>
                <p class="text-green-100 text-xs mt-1"><span id="todayCount">0</span> ta to'lov</p>
            </div>
            
            <!-- Oylik to'lovlar -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-blue-100 text-sm">Shu Oylik</p>
                    <i class="fas fa-calendar-alt text-2xl opacity-50"></i>
                </div>
                <h3 class="text-2xl font-bold" id="monthAmount">0 so'm</h3>
                <p class="text-blue-100 text-xs mt-1"><span id="monthCount">0</span> ta to'lov</p>
            </div>
            
            <!-- Jami to'lovlar -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-purple-100 text-sm">Jami To'lovlar</p>
                    <i class="fas fa-money-bill-wave text-2xl opacity-50"></i>
                </div>
                <h3 class="text-2xl font-bold" id="totalAmount">0 so'm</h3>
                <p class="text-purple-100 text-xs mt-1"><span id="totalCount">0</span> ta to'lov</p>
            </div>
            
            <!-- Nasiya qarzlar -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-orange-100 text-sm">Nasiya Qarzlar</p>
                    <i class="fas fa-exclamation-triangle text-2xl opacity-50"></i>
                </div>
                <h3 class="text-2xl font-bold" id="nasiyaAmount">0 so'm</h3>
                <p class="text-orange-100 text-xs mt-1"><span id="nasiyaCount">0</span> ta qarz</p>
            </div>
            
        </div>
        
        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="flex border-b">
                <button onclick="switchTab('all')" id="tabAll" class="flex-1 px-6 py-4 font-semibold transition bg-gradient-to-r from-purple-500 to-pink-600 text-white">
                    <i class="fas fa-receipt mr-2"></i>Barcha To'lovlar
                </button>
                <button onclick="switchTab('nasiya')" id="tabNasiya" class="flex-1 px-6 py-4 font-semibold transition bg-gray-100 text-gray-700 hover:bg-gray-200">
                    <i class="fas fa-clock mr-2"></i>Nasiya Qarzlar
                </button>
            </div>
        </div>
        
        <!-- Content: All Payments -->
        <div id="contentAll">
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <input 
                            type="text" 
                            id="searchInput" 
                            placeholder="Mijoz nomi..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                        >
                    </div>
                    
                    <!-- Payment Method -->
                    <div>
                        <select id="methodFilter" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="all">Hammasi</option>
                            <option value="naqd">Naqd</option>
                            <option value="karta">Karta</option>
                            <option value="nasiya">Nasiya</option>
                            <option value="bepul">Bepul</option>
                        </select>
                    </div>
                    
                    <!-- Date From -->
                    <div>
                        <input 
                            type="date" 
                            id="dateFrom"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                        >
                    </div>
                    
                    <!-- Date To -->
                    <div>
                        <input 
                            type="date" 
                            id="dateTo"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                        >
                    </div>
                    
                </div>
                
                <div class="flex space-x-2 mt-4">
                    <button onclick="applyFilters()" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    <button onclick="resetFilters()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                        <i class="fas fa-redo mr-2"></i>Tozalash
                    </button>
                </div>
            </div>
            
            <!-- Payments Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Mijoz</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Izoh</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Summa</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Turi</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Sana</th>
                            </tr>
                        </thead>
                        <tbody id="paymentsTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500 mx-auto mb-4"></div>
                                    <p class="text-gray-500">Yuklanmoqda...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t">
                    <div class="text-sm text-gray-600">
                        <span id="paginationInfo">0 dan 0 gacha</span>
                    </div>
                    <div id="paginationButtons" class="flex space-x-2"></div>
                </div>
            </div>
        </div>
        
        <!-- Content: Nasiya Payments -->
        <div id="contentNasiya" class="hidden">
            <!-- Nasiya Search -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <input 
                    type="text" 
                    id="nasiyaSearch" 
                    placeholder="Mijoz nomi yoki telefon..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                >
            </div>
            
            <!-- Nasiya Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Mijoz</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Paket</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Qarz summasi</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Sana</th>
                                <th class="text-left py-4 px-6 font-semibold text-gray-700">Izoh</th>
                            </tr>
                        </thead>
                        <tbody id="nasiyaTableBody">
                            <tr>
                                <td colspan="5" class="text-center py-12">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
                                    <p class="text-gray-500">Yuklanmoqda...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Nasiya Pagination -->
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-between border-t">
                    <div class="text-sm text-gray-600">
                        <span id="nasiyaPaginationInfo">0 dan 0 gacha</span>
                    </div>
                    <div id="nasiyaPaginationButtons" class="flex space-x-2"></div>
                </div>
            </div>
        </div>
        
    </main>
    
</div>

<script>
let currentTab = 'all';
let currentPage = 1;
let searchTimeout;

// Tab switch
function switchTab(tab) {
    currentTab = tab;
    
    // Update tab buttons
    const tabAll = document.getElementById('tabAll');
    const tabNasiya = document.getElementById('tabNasiya');
    
    if (tab === 'all') {
        tabAll.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-pink-600', 'text-white');
        tabAll.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
        tabNasiya.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-pink-600', 'text-white');
        tabNasiya.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
        
        document.getElementById('contentAll').classList.remove('hidden');
        document.getElementById('contentNasiya').classList.add('hidden');
        
        loadPayments(1);
    } else {
        tabNasiya.classList.add('bg-gradient-to-r', 'from-purple-500', 'to-pink-600', 'text-white');
        tabNasiya.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
        tabAll.classList.remove('bg-gradient-to-r', 'from-purple-500', 'to-pink-600', 'text-white');
        tabAll.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
        
        document.getElementById('contentAll').classList.add('hidden');
        document.getElementById('contentNasiya').classList.remove('hidden');
        
        loadNasiyaPayments(1);
    }
}

// Statistikani yuklash
async function loadDashboardStats() {
    try {
        const response = await fetch('../../backend/statistics/dashboard.php');
        const result = await response.json();
        
        if (result.success) {
            const data = result.data;
            document.getElementById('todayAmount').textContent = data.payments.today_amount || '0 so\'m';
            document.getElementById('todayCount').textContent = data.payments.today_count || 0;
            document.getElementById('monthAmount').textContent = data.payments.month_amount || '0 so\'m';
            document.getElementById('monthCount').textContent = data.payments.month_count || 0;
            document.getElementById('totalAmount').textContent = data.payments.total_amount || '0 so\'m';
            document.getElementById('totalCount').textContent = data.payments.total_count || 0;
        }
        
        // Nasiya statistika
        const nasiyaResponse = await fetch('../../backend/payments/nasiya.php?page=1');
        const nasiyaResult = await nasiyaResponse.json();
        
        if (nasiyaResult.success) {
            document.getElementById('nasiyaAmount').textContent = nasiyaResult.data.statistics.total_debt || '0 so\'m';
            document.getElementById('nasiyaCount').textContent = nasiyaResult.data.statistics.total_count || 0;
        }
    } catch (error) {
        console.error('Stats loading error:', error);
    }
}

// ========== ALL PAYMENTS ==========

// Load payments
async function loadPayments(page = 1) {
    const search = document.getElementById('searchInput').value;
    const method = document.getElementById('methodFilter').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    try {
        const url = new URL('../../backend/payments/read.php', window.location.origin);
        url.searchParams.append('page', page);
        url.searchParams.append('search', search);
        url.searchParams.append('payment_method', method);
        if (dateFrom) url.searchParams.append('date_from', dateFrom);
        if (dateTo) url.searchParams.append('date_to', dateTo);
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            renderPayments(result.data.payments);
            renderPagination(result.data.pagination);
        }
    } catch (error) {
        console.error('Load payments error:', error);
        showToast('To\'lovlarni yuklashda xatolik', 'error');
    }
}

function renderPayments(payments) {
    const tbody = document.getElementById('paymentsTableBody');
    
    if (payments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-12 text-gray-500">
                    <i class="fas fa-receipt text-5xl mb-3 opacity-50"></i>
                    <p class="text-lg">To'lovlar topilmadi</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = payments.map(payment => `
        <tr class="border-b hover:bg-gray-50 transition">
            <td class="py-4 px-6">
                <div>
                    <p class="font-semibold text-gray-800">${payment.customer_name}</p>
                    <p class="text-xs text-gray-500">${payment.customer_phone}</p>
                </div>
            </td>
            <td class="py-4 px-6">
                <span class="text-gray-700 text-sm">${payment.notes || '-'}</span>
            </td>
            <td class="py-4 px-6">
                <span class="font-bold text-green-600">${payment.amount} so'm</span>
            </td>
            <td class="py-4 px-6">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getPaymentBadge(payment.payment_method)}">
                    ${payment.payment_method.toUpperCase()}
                </span>
            </td>
            <td class="py-4 px-6 text-sm text-gray-600">
                ${payment.payment_date}
            </td>
        </tr>
    `).join('');
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationButtons');
    const info = document.getElementById('paginationInfo');
    
    const start = (pagination.current_page - 1) * pagination.items_per_page + 1;
    const end = Math.min(start + pagination.items_per_page - 1, pagination.total_items);
    info.textContent = `${start} dan ${end} gacha, jami ${pagination.total_items} ta`;
    
    let html = '';
    
    html += `
        <button 
            onclick="loadPayments(${pagination.current_page - 1})" 
            ${!pagination.has_prev ? 'disabled' : ''}
            class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 disabled:opacity-50"
        >
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `
                <button 
                    onclick="loadPayments(${i})"
                    class="px-4 py-2 ${i === pagination.current_page ? 'bg-purple-500 text-white' : 'bg-white border hover:bg-gray-50'} rounded-lg"
                >
                    ${i}
                </button>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += '<span class="px-2">...</span>';
        }
    }
    
    html += `
        <button 
            onclick="loadPayments(${pagination.current_page + 1})" 
            ${!pagination.has_next ? 'disabled' : ''}
            class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 disabled:opacity-50"
        >
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    container.innerHTML = html;
}

function applyFilters() {
    currentPage = 1;
    loadPayments(1);
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('methodFilter').value = 'all';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    currentPage = 1;
    loadPayments(1);
}

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadPayments(1);
    }, 500);
});

// ========== NASIYA PAYMENTS ==========

async function loadNasiyaPayments(page = 1) {
    const search = document.getElementById('nasiyaSearch').value;
    
    try {
        const url = new URL('../../backend/payments/nasiya.php', window.location.origin);
        url.searchParams.append('page', page);
        if (search) url.searchParams.append('search', search);
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            renderNasiyaPayments(result.data.nasiya_payments);
            renderNasiyaPagination(result.data.pagination);
        }
    } catch (error) {
        console.error('Load nasiya error:', error);
        showToast('Nasiya to\'lovlarni yuklashda xatolik', 'error');
    }
}

function renderNasiyaPayments(payments) {
    const tbody = document.getElementById('nasiyaTableBody');
    
    if (payments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-12 text-gray-500">
                    <i class="fas fa-smile text-5xl mb-3 opacity-50"></i>
                    <p class="text-lg">Nasiya qarzlar yo'q!</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = payments.map(payment => `
        <tr class="border-b hover:bg-orange-50 transition">
            <td class="py-4 px-6">
                <div>
                    <p class="font-semibold text-gray-800">${payment.customer_name}</p>
                    <p class="text-xs text-gray-500">${payment.phone}</p>
                </div>
            </td>
            <td class="py-4 px-6">
                <div>
                    <p class="text-sm">${payment.package_info.total_ads} ta reklama</p>
                    <p class="text-xs text-gray-500">Qoldiq: ${payment.package_info.remaining_ads}</p>
                </div>
            </td>
            <td class="py-4 px-6">
                <span class="font-bold text-orange-600 text-lg">${payment.amount}</span>
            </td>
            <td class="py-4 px-6 text-sm text-gray-600">
                ${payment.payment_date}
            </td>
            <td class="py-4 px-6 text-sm text-gray-600">
                ${payment.notes || '-'}
            </td>
        </tr>
    `).join('');
}

function renderNasiyaPagination(pagination) {
    const container = document.getElementById('nasiyaPaginationButtons');
    const info = document.getElementById('nasiyaPaginationInfo');
    
    const start = (pagination.current_page - 1) * pagination.items_per_page + 1;
    const end = Math.min(start + pagination.items_per_page - 1, pagination.total_items);
    info.textContent = `${start} dan ${end} gacha, jami ${pagination.total_items} ta`;
    
    let html = '';
    
    html += `
        <button 
            onclick="loadNasiyaPayments(${pagination.current_page - 1})" 
            ${!pagination.has_prev ? 'disabled' : ''}
            class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 disabled:opacity-50"
        >
            <i class="fas fa-chevron-left"></i>
        </button>
    `;
    
    for (let i = 1; i <= pagination.total_pages; i++) {
        if (i === 1 || i === pagination.total_pages || (i >= pagination.current_page - 2 && i <= pagination.current_page + 2)) {
            html += `
                <button 
                    onclick="loadNasiyaPayments(${i})"
                    class="px-4 py-2 ${i === pagination.current_page ? 'bg-orange-500 text-white' : 'bg-white border hover:bg-gray-50'} rounded-lg"
                >
                    ${i}
                </button>
            `;
        } else if (i === pagination.current_page - 3 || i === pagination.current_page + 3) {
            html += '<span class="px-2">...</span>';
        }
    }
    
    html += `
        <button 
            onclick="loadNasiyaPayments(${pagination.current_page + 1})" 
            ${!pagination.has_next ? 'disabled' : ''}
            class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50 disabled:opacity-50"
        >
            <i class="fas fa-chevron-right"></i>
        </button>
    `;
    
    container.innerHTML = html;
}

document.getElementById('nasiyaSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadNasiyaPayments(1);
    }, 500);
});

// ========== HELPERS ==========

function getPaymentBadge(method) {
    const badges = {
        'naqd': 'bg-green-100 text-green-800',
        'karta': 'bg-blue-100 text-blue-800',
        'nasiya': 'bg-orange-100 text-orange-800',
        'bepul': 'bg-purple-100 text-purple-800'
    };
    return badges[method] || 'bg-gray-100 text-gray-800';
}

// Load on page ready
document.addEventListener('DOMContentLoaded', function() {
    loadPayments(1);
    loadDashboardStats();
});
</script>

<?php include '../components/footer.php'; ?>