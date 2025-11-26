/**
 * Packages Grouped JavaScript
 * Mijozlar bo'yicha guruhlangan paketlar
 */

let groupedPackages = [];
let allCustomers = [];
let expandedCustomers = new Set(); // Qaysi mijozlar ochiq

// Load customers for dropdown
async function loadCustomersDropdown() {
    try {
        const response = await fetch('../../backend/customers/read.php?status=active');
        const result = await response.json();

        if (result.success) {
            allCustomers = result.data.customers;
            renderCustomersDropdown();
        }
    } catch (error) {
        console.error('Load customers error:', error);
    }
}

function renderCustomersDropdown() {
    const select = document.getElementById('selectedCustomer');
    select.innerHTML = '<option value="">Mijozni tanlang...</option>';

    allCustomers.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = `${customer.ad_name} - ${customer.phone}`;
        select.appendChild(option);
    });
}

// Load packages (grouped by customer)
async function loadPackages() {
    try {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;

        const url = new URL('../../backend/packages/read_grouped.php', window.location.origin);
        url.searchParams.append('status', status);
        if (search) url.searchParams.append('search', search);

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            groupedPackages = result.data.grouped_packages;
            renderGroupedPackages();
        }
    } catch (error) {
        console.error('Load packages error:', error);
        showToast('Paketlarni yuklashda xatolik', 'error');
    }
}

function renderGroupedPackages() {
    const container = document.getElementById('packagesContainer');

    if (groupedPackages.length === 0) {
        container.innerHTML = `
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <i class="fas fa-box text-5xl text-gray-300 mb-4"></i>
                <p class="text-lg text-gray-500">Paketlar topilmadi</p>
            </div>
        `;
        return;
    }

    container.innerHTML = groupedPackages.map(customer => {
        const isExpanded = expandedCustomers.has(customer.customer_id);
        const totalRemaining = customer.packages.reduce((sum, pkg) =>
            pkg.status === 'active' ? sum + pkg.remaining_ads : sum, 0
        );

        return `
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                
                <!-- Customer Header (Main Row) -->
                <div class="bg-gradient-to-r from-green-50 to-teal-50 p-4 cursor-pointer hover:bg-green-100 transition"
                     onclick="toggleCustomer(${customer.customer_id})">
                    <div class="flex items-center justify-between">
                        
                        <!-- Left: Customer Info -->
                        <div class="flex items-center space-x-4">
                            <div class="w-14 h-14 bg-gradient-to-r from-green-400 to-teal-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
                                ${customer.customer_name.substring(0, 2).toUpperCase()}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">${customer.customer_name}</h3>
                                <p class="text-sm text-gray-600">${customer.phone}</p>
                            </div>
                        </div>
                        
                        <!-- Middle: Stats -->
                        <div class="flex items-center space-x-6">
                            
                            <!-- Active Packages -->
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Aktiv paketlar</p>
                                <p class="text-2xl font-bold text-green-600">${customer.active_packages}</p>
                            </div>
                            
                            <!-- Total Remaining -->
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Jami qoldiq</p>
                                <p class="text-2xl font-bold ${totalRemaining > 0 ? 'text-blue-600' : 'text-gray-400'}">${totalRemaining}</p>
                            </div>
                            
                            <!-- Completed Packages -->
                            ${customer.completed_packages > 0 ? `
                            <div class="text-center">
                                <p class="text-sm text-gray-600">Tugagan</p>
                                <p class="text-2xl font-bold text-gray-400">${customer.completed_packages}</p>
                            </div>
                            ` : ''}
                            
                        </div>
                        
                        <!-- Right: Expand Icon -->
                        <div class="text-gray-600">
                            <i class="fas fa-chevron-${isExpanded ? 'up' : 'down'} text-xl"></i>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Expanded Packages List -->
                ${isExpanded ? `
                <div class="border-t">
                    <div class="p-4 space-y-3">
                        ${customer.packages.map(pkg => renderPackageCard(pkg)).join('')}
                    </div>
                </div>
                ` : ''}
                
            </div>
        `;
    }).join('');
}

function renderPackageCard(pkg) {
    const progress = (pkg.used_ads / pkg.total_ads) * 100;
    const progressColor = progress < 50 ? 'bg-green-500' : progress < 80 ? 'bg-yellow-500' : 'bg-red-500';
    const statusBadge = pkg.status === 'active'
        ? '<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">Aktiv</span>'
        : '<span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">Tugagan</span>';

    return `
        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
            
            <!-- Left: Package Info -->
            <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                    <span class="text-lg font-bold text-gray-800">${pkg.total_ads} ta reklama</span>
                    ${statusBadge}
                </div>
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                    <div class="${progressColor} h-2 rounded-full transition-all" style="width: ${progress}%"></div>
                </div>
                
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <span>
                        <i class="fas fa-chart-line mr-1"></i>
                        Ishlatilgan: <strong>${pkg.used_ads}</strong>
                    </span>
                    <span class="${pkg.remaining_ads > 0 ? 'text-green-600' : 'text-red-600'} font-semibold">
                        <i class="fas fa-box mr-1"></i>
                        Qoldiq: <strong>${pkg.remaining_ads}</strong>
                    </span>
                </div>
            </div>
            
            <!-- Middle: Payment -->
            <div class="mx-6 text-center">
                <p class="text-lg font-bold text-gray-800">${pkg.payment_amount}</p>
                <span class="text-xs px-2 py-1 rounded ${getPaymentMethodBadge(pkg.payment_method)}">
                    ${getPaymentMethodText(pkg.payment_method)}
                </span>
            </div>
            
            <!-- Right: Date & Actions -->
            <div class="text-right">
                <p class="text-sm text-gray-600 mb-2">${pkg.created_at}</p>
                <div class="flex items-center justify-end space-x-2">
                    ${pkg.status === 'active' ? `
                    <button onclick="editPackage(${pkg.id}, event)" class="text-blue-600 hover:text-blue-700 p-2" title="Tahrirlash">
                        <i class="fas fa-edit"></i>
                    </button>
                    ` : ''}
                    <button onclick="viewPackage(${pkg.id}, event)" class="text-green-600 hover:text-green-700 p-2" title="Ko'rish">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${pkg.used_ads === 0 ? `
                    <button onclick="deletePackage(${pkg.id}, '${pkg.customer_name}', event)" class="text-red-600 hover:text-red-700 p-2" title="O'chirish">
                        <i class="fas fa-trash"></i>
                    </button>
                    ` : ''}
                </div>
            </div>
            
        </div>
    `;
}

function getPaymentMethodBadge(method) {
    const badges = {
        'naqd': 'bg-green-100 text-green-800',
        'karta': 'bg-blue-100 text-blue-800',
        'nasiya': 'bg-yellow-100 text-yellow-800',
        'bepul': 'bg-purple-100 text-purple-800'
    };
    return badges[method] || 'bg-gray-100 text-gray-800';
}

function getPaymentMethodText(method) {
    const texts = {
        'naqd': 'Naqd',
        'karta': 'Karta',
        'nasiya': 'Nasiya',
        'bepul': 'Bepul'
    };
    return texts[method] || method;
}

// Toggle customer expand/collapse
function toggleCustomer(customerId) {
    if (expandedCustomers.has(customerId)) {
        expandedCustomers.delete(customerId);
    } else {
        expandedCustomers.add(customerId);
    }
    renderGroupedPackages();
}

// Toggle payment amount field
function togglePaymentAmount() {
    const method = document.getElementById('paymentMethod').value;
    const amountField = document.getElementById('paymentAmountField');
    const priceInput = document.getElementById('packagePrice');

    if (method === 'bepul') {
        amountField.style.display = 'none';
        priceInput.value = '0';
        priceInput.removeAttribute('required');
    } else {
        amountField.style.display = 'block';
        priceInput.setAttribute('required', 'required');
        if (priceInput.value === '0') {
            priceInput.value = '';
        }
    }
}

// Modal functions
function openNewPackageModal() {
    document.getElementById('packageForm').reset();
    document.getElementById('paymentMethod').value = 'naqd';
    togglePaymentAmount();
    document.getElementById('packageModal').classList.remove('hidden');
    loadCustomersDropdown();
}

function closePackageModal() {
    document.getElementById('packageModal').classList.add('hidden');
}

// Quick add customer
function openQuickAddCustomer() {
    document.getElementById('quickCustomerForm').reset();
    document.getElementById('quickCustomerModal').classList.remove('hidden');
}

function closeQuickCustomerModal() {
    document.getElementById('quickCustomerModal').classList.add('hidden');
}

document.getElementById('quickCustomerForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('ad_name', document.getElementById('quickCustomerName').value);
    formData.append('phone', document.getElementById('quickCustomerPhone').value);

    try {
        showLoading();
        const response = await fetch('../../backend/customers/create.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Mijoz qo\'shildi', 'success');
            closeQuickCustomerModal();
            await loadCustomersDropdown();
            document.getElementById('selectedCustomer').value = result.data.id;
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
});

// Package form submit
document.getElementById('packageForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    if (formData.get('payment_method') === 'bepul') {
        formData.set('price', '0');
    }

    try {
        showLoading();
        const response = await fetch('../../backend/packages/create.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Paket muvaffaqiyatli qo\'shildi', 'success');
            closePackageModal();
            loadPackages();
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
});

// Edit package
async function editPackage(id, event) {
    event.stopPropagation(); // Parent expand ni ishlatmasin

    try {
        showLoading();
        const response = await fetch(`../../backend/packages/get_by_id.php?id=${id}`);
        const result = await response.json();
        hideLoading();

        if (result.success) {
            const pkg = result.data.package;

            document.getElementById('editPackageId').value = pkg.id;
            document.getElementById('editTotalAds').value = pkg.total_ads;
            document.getElementById('editTotalAds').min = pkg.used_ads; // Ishlatilgandan kam bo'lmasin
            document.getElementById('editUsedAds').textContent = pkg.used_ads;

            document.getElementById('editPackageModal').classList.remove('hidden');
        }
    } catch (error) {
        hideLoading();
        showToast('Ma\'lumotlarni olishda xatolik', 'error');
    }
}

function closeEditModal() {
    document.getElementById('editPackageModal').classList.add('hidden');
}

// Edit form submit
document.getElementById('editPackageForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('id', document.getElementById('editPackageId').value);
    formData.append('total_ads', document.getElementById('editTotalAds').value);

    try {
        showLoading();
        const response = await fetch('../../backend/packages/update.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Paket yangilandi', 'success');
            closeEditModal();
            loadPackages();
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
});

// View package
function viewPackage(id, event) {
    event.stopPropagation();
    showToast('Ko\'rish funksiyasi tez orada qo\'shiladi', 'info');
}

// Delete package
async function deletePackage(id, name, event) {
    event.stopPropagation();

    if (!confirm(`${name} paketini o'chirmoqchimisiz?`)) return;

    try {
        showLoading();
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('../../backend/packages/delete.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Paket o\'chirildi', 'success');
            loadPackages();
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('O\'chirishda xatolik', 'error');
    }
}

// Search and filters
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadPackages();
    }, 500);
});

document.getElementById('statusFilter').addEventListener('change', function () {
    loadPackages();
});

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('statusFilter').value = 'active';
    loadPackages();
}

// Initialize
document.addEventListener('DOMContentLoaded', function () {
    loadPackages();
});