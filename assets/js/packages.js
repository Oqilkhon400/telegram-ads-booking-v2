/**
 * Packages Management - FULL VERSION
 * Paketlar boshqaruvi
 */

console.log('📦 Packages.js - FULL VERSION yuklandi');

// Load active packages
async function loadActivePackages() {
    try {
        showLoading();
        
        // Get customers with packages
        const response = await fetch('../../backend/customers/read.php?status=active&limit=1000');
        const result = await response.json();
        
        hideLoading();
        
        if (result.success && result.data.customers) {
            const customers = result.data.customers;
            
            // Filter customers who have packages
            const withPackages = customers.filter(c => c.total_packages > 0);
            
            renderPackagesTable(withPackages);
            document.getElementById('totalPackagesCount').textContent = `${withPackages.length} ta`;
        } else {
            document.getElementById('activePackagesTable').innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Hozircha paket yo'q</p>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        hideLoading();
        console.error('Load packages error:', error);
        showToast('Paketlarni yuklashda xatolik', 'error');
    }
}

// Render packages table
function renderPackagesTable(customers) {
    const tbody = document.getElementById('activePackagesTable');
    
    if (customers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-8 text-gray-500">
                    <i class="fas fa-inbox text-4xl mb-2"></i>
                    <p>Hozircha paket yo'q</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = customers.map(customer => `
        <tr class="border-b hover:bg-gray-50 transition">
            <td class="py-3 px-4">
                <p class="font-semibold text-blue-600">${customer.ad_name || 'N/A'}</p>
                ${customer.contact_person ? `<p class="text-sm text-gray-600">${customer.contact_person}</p>` : ''}
                <p class="text-xs text-gray-500">${customer.phone || ''}</p>
            </td>
            <td class="py-3 px-4">
                <span class="text-gray-700 font-medium">${customer.total_packages || 0} ta paket</span>
            </td>
            <td class="py-3 px-4">
                <span class="font-bold text-green-600">${customer.total_remaining_ads || 0} reklama</span>
            </td>
            <td class="py-3 px-4">
                <span class="text-gray-600">-</span>
            </td>
            <td class="py-3 px-4">
                <span class="text-xs text-gray-500">${customer.created_at || ''}</span>
            </td>
            <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end space-x-2">
                    <button onclick="viewCustomerPackages(${customer.id})" class="text-blue-600 hover:text-blue-700 p-2" title="Ko'rish">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="editCustomerPackage(${customer.id})" class="text-green-600 hover:text-green-700 p-2" title="Tahrirlash">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteCustomerPackage(${customer.id}, '${customer.ad_name}')" class="text-red-600 hover:text-red-700 p-2" title="O'chirish">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// View customer packages
async function viewCustomerPackages(customerId) {
    try {
        showLoading();
        const response = await fetch(`../../backend/customers/get_by_id.php?id=${customerId}`);
        const result = await response.json();
        hideLoading();
        
        if (result.success) {
            const customer = result.data.customer;
            const packages = result.data.packages || [];
            
            let packagesHtml = packages.map(p => `
                <div class="border-b py-3">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-semibold">${p.total_ads} ta reklama</p>
                            <p class="text-sm text-gray-600">Ishlatilgan: ${p.used_ads} | Qolgan: ${p.remaining_ads}</p>
                        </div>
                        <span class="px-3 py-1 rounded text-sm ${p.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                            ${p.status === 'active' ? 'Faol' : 'Tugagan'}
                        </span>
                    </div>
                </div>
            `).join('');
            
            if (packages.length === 0) {
                packagesHtml = '<p class="text-gray-500 text-center py-4">Paket topilmadi</p>';
            }
            
            showAlert(`
                <div class="space-y-4">
                    <div class="border-b pb-3">
                        <h4 class="font-bold text-lg">${customer.ad_name}</h4>
                        ${customer.contact_person ? `<p class="text-gray-600">${customer.contact_person}</p>` : ''}
                        <p class="text-gray-500">${customer.phone}</p>
                    </div>
                    <div>
                        <h5 class="font-semibold mb-2">Paketlar:</h5>
                        ${packagesHtml}
                    </div>
                </div>
            `, 'info');
        }
    } catch (error) {
        hideLoading();
        showToast('Ma\'lumotlarni olishda xatolik', 'error');
    }
}

// Edit customer package
async function editCustomerPackage(customerId) {
    showToast('Tahrirlash funksiyasi hozircha mavjud emas', 'warning');
}

// Delete customer package
async function deleteCustomerPackage(customerId, customerName) {
    if (!confirm(`${customerName} ning paketlarini o'chirmoqchimisiz?`)) {
        return;
    }
    
    showToast('O\'chirish funksiyasi hozircha mavjud emas', 'warning');
}

// Open modal
async function openNewPackageModal() {
    try {
        showLoading();
        const response = await fetch('../../backend/customers/read.php?status=active&limit=1000');
        const result = await response.json();
        hideLoading();

        console.log('📊 Customers loaded:', result);

        if (result.success && result.data && result.data.customers) {
            const select = document.getElementById('selectedCustomer');
            select.innerHTML = '<option value="">Mijozni tanlang...</option>' +
                result.data.customers.map(c => {
                    const adName = c.ad_name || 'Noma\'lum';
                    const contactPerson = c.contact_person ? ` (${c.contact_person})` : '';
                    const phone = c.phone || '';
                    return `<option value="${c.id}">${adName}${contactPerson} - ${phone}</option>`;
                }).join('');
            
            console.log('✅ Dropdown populated with', result.data.customers.length, 'customers');
        } else {
            console.error('❌ Invalid response:', result);
            showToast('Mijozlarni yuklashda xatolik', 'error');
        }

        document.getElementById('packageForm').reset();
        document.getElementById('selectedCustomer').value = '';
        document.getElementById('packageModal').classList.remove('hidden');
    } catch (error) {
        hideLoading();
        console.error('❌ Load customers error:', error);
        showToast('Mijozlarni yuklashda xatolik', 'error');
    }
}

// Close modal
function closePackageModal() {
    document.getElementById('packageModal').classList.add('hidden');
}

// Quick customer modal
function openQuickAddCustomer() {
    document.getElementById('quickCustomerModal').classList.remove('hidden');
}

function closeQuickCustomerModal() {
    document.getElementById('quickCustomerModal').classList.add('hidden');
}

// Submit package form
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM ready - packages');
    
    const packageForm = document.getElementById('packageForm');
    const quickCustomerForm = document.getElementById('quickCustomerForm');
    
    console.log('📋 packageForm:', packageForm);
    console.log('📋 quickCustomerForm:', quickCustomerForm);
    
    if (packageForm) {
        console.log('✅ Adding submit listener to packageForm');
        packageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('📤 Package form submitted');
            
            const customerId = document.getElementById('selectedCustomer').value;
            const totalAds = document.getElementById('packageTotalAds').value;
            const price = document.getElementById('packagePrice').value;
            const paymentMethod = document.getElementById('paymentMethod').value;
            const paymentDate = document.getElementById('paymentDate').value;
            const notes = document.getElementById('paymentNotes').value;
            
            if (!customerId) {
                showToast('Mijozni tanlang', 'error');
                return;
            }
            
            try {
                showLoading();
                
                // 1. Create package
                const packageData = new FormData();
                packageData.append('total_ads', totalAds);
                packageData.append('price', price);
                
                const packageResponse = await fetch('../../backend/packages/create.php', {
                    method: 'POST',
                    body: packageData
                });
                const packageResult = await packageResponse.json();
                
                if (!packageResult.success) {
                    throw new Error(packageResult.error || 'Paket yaratishda xatolik');
                }
                
                const packageId = packageResult.data.id;
                
                // 2. Assign package to customer
                const assignData = new FormData();
                assignData.append('customer_id', customerId);
                assignData.append('package_id', packageId);
                
                const assignResponse = await fetch('../../backend/packages/assign_to_customer.php', {
                    method: 'POST',
                    body: assignData
                });
                const assignResult = await assignResponse.json();
                
                if (!assignResult.success) {
                    throw new Error(assignResult.error || 'Paket biriktirishda xatolik');
                }
                
                const customerPackageId = assignResult.data.id;
                
                // 3. Create payment
                const paymentData = new FormData();
                paymentData.append('customer_id', customerId);
                paymentData.append('customer_package_id', customerPackageId);
                paymentData.append('amount', price);
                paymentData.append('payment_method', paymentMethod);
                paymentData.append('payment_date', paymentDate);
                paymentData.append('notes', notes);
                
                const paymentResponse = await fetch('../../backend/payments/create.php', {
                    method: 'POST',
                    body: paymentData
                });
                const paymentResult = await paymentResponse.json();
                
                hideLoading();
                
                if (paymentResult.success) {
                    showToast('Paket va to\'lov muvaffaqiyatli qo\'shildi!', 'success');
                    closePackageModal();
                    loadActivePackages();
                } else {
                    showToast('To\'lov qo\'shishda xatolik: ' + (paymentResult.error || ''), 'warning');
                    loadActivePackages(); // Paket qo'shilgan, faqat to'lov xato
                }
                
            } catch (error) {
                hideLoading();
                console.error('Error:', error);
                showToast(error.message || 'Xatolik yuz berdi', 'error');
            }
        });
    }
    
    // Quick customer form
    if (quickCustomerForm) {
        quickCustomerForm.addEventListener('submit', async function (e) {
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
                    showToast('Mijoz qo\'shildi!', 'success');
                    closeQuickCustomerModal();

                    const select = document.getElementById('selectedCustomer');
                    const option = new Option(
                        `${result.data.ad_name} - ${result.data.phone}`,
                        result.data.id
                    );
                    select.add(option);
                    select.value = result.data.id;
                } else {
                    showToast(result.error, 'error');
                }
            } catch (error) {
                hideLoading();
                showToast('Xatolik yuz berdi', 'error');
            }
        });
    }
    
    // Load packages on page load
    loadActivePackages();
});

console.log('📦 packages.js loaded successfully - FULL VERSION');
