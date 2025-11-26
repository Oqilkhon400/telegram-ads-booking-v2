let allBookingsData = [];
let allCustomers = [];
let searchTimeout = null;

// ==========================================
// KALENDAR FUNKSIYALARI
// ==========================================

async function loadCalendar() {
    const selectedDate = document.getElementById('selectedDate').value;

    try {
        const response = await fetch(`../../backend/calendar/get_slots_by_date.php?date=${selectedDate}`);
        const result = await response.json();

        if (result.success) {
            allBookingsData = result.data.time_slots;

            allBookingsData.forEach(slot => {
                const slotDateTime = new Date(selectedDate + ' ' + slot.time);
                slot.is_past = slotDateTime < new Date();
            });

            renderCalendar(result.data);
            updateDateTitle(selectedDate);
            updateStatistics(result.data.statistics);
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        console.error('Calendar load error:', error);
        showToast('Kalendar yuklanmadi', 'error');
    }
}

function renderCalendar(data) {
    const grid = document.getElementById('timeSlotsGrid');

    if (!data.time_slots || data.time_slots.length === 0) {
        grid.innerHTML = `
            <div class="col-span-full text-center py-12 text-gray-500">
                <i class="fas fa-calendar-times text-5xl mb-3 opacity-50"></i>
                <p class="text-lg">Bu kunda hech qanday vaqt topilmadi</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = data.time_slots.map(slot => {
        const slotClass = getSlotClass(slot);
        const slotContent = getSlotContent(slot);

        return `
            <div class="${slotClass}" 
                 onclick="${slot.is_booked ? `showBookingDetails(${slot.booking.id})` : `quickBook('${slot.time}')`}">
                ${slotContent}
            </div>
        `;
    }).join('');
}

function getSlotClass(slot) {
    const baseClass = "p-4 rounded-lg border-2 transition transform hover:scale-105 cursor-pointer shadow-md";

    if (slot.is_past) {
        return `${baseClass} bg-gray-100 border-gray-300 opacity-60`;
    }

    if (slot.is_booked) {
        return `${baseClass} bg-blue-50 border-blue-300 hover:border-blue-500`;
    }

    return `${baseClass} bg-green-50 border-green-300 hover:border-green-500`;
}

function getSlotContent(slot) {
    const time = `<div class="text-xl font-bold mb-2">${slot.time}</div>`;

    if (slot.is_past && !slot.is_booked) {
        return `
            ${time}
            <div class="text-gray-500 text-sm">
                <i class="fas fa-history mr-1"></i>O'tgan
            </div>
        `;
    }

    if (!slot.is_booked) {
        return `
            ${time}
            <div class="text-green-600 font-semibold text-sm">
                <i class="fas fa-check-circle mr-1"></i>Bo'sh
            </div>
        `;
    }

    const booking = slot.booking;
    const progressColor = getProgressColor(booking.package_used, booking.package_total);
    const progressText = `${booking.package_used}/${booking.package_total}`;
    const pastClass = slot.is_past ? 'opacity-75' : '';

    // Izoh bo'lsa ko'rsatish, bo'lmasa "Izohsiz"
    const description = (booking.ad_description && booking.ad_description !== '-')
        ? booking.ad_description
        : '<span class="text-gray-400 italic">Izohsiz</span>';

    return `
        ${time}
        <div class="text-gray-700 font-semibold text-sm mb-1 truncate ${pastClass}">
            <i class="fas fa-user mr-1"></i>${booking.customer_name}
        </div>
        <div class="text-gray-600 text-xs mb-2 truncate ${pastClass}">
            ${description}
        </div>
        <div class="flex items-center justify-between">
            <span class="inline-block px-2 py-1 rounded text-xs font-bold ${progressColor}">
                ${progressText}
            </span>
            ${slot.is_past ? '<span class="text-xs text-gray-400"><i class="fas fa-history"></i></span>' : '<span class="text-xs text-gray-500"><i class="fas fa-info-circle"></i></span>'}
        </div>
    `;
}

function getProgressColor(used, total) {
    const percentage = (used / total) * 100;

    if (percentage <= 33) {
        return 'bg-green-500 text-white';
    } else if (percentage <= 66) {
        return 'bg-yellow-500 text-white';
    } else {
        return 'bg-red-500 text-white';
    }
}

function updateDateTitle(date) {
    const dateObj = new Date(date);
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const formatted = dateObj.toLocaleDateString('uz-UZ', options);

    document.getElementById('dateTitle').textContent = formatted;

    const today = new Date().toDateString();
    const selectedDateObj = new Date(date).toDateString();

    if (today === selectedDateObj) {
        document.getElementById('dateSubtitle').innerHTML = '<span class="text-blue-600 font-semibold"><i class="fas fa-calendar-day mr-1"></i>Bugun</span>';
    } else {
        document.getElementById('dateSubtitle').textContent = '';
    }
}

function updateStatistics(stats) {
    document.getElementById('availableCount').textContent = stats.available || 0;
    document.getElementById('bookedCount').textContent = stats.booked || 0;
    document.getElementById('pastCount').textContent = stats.past || 0;
}

function changeDate(days) {
    const dateInput = document.getElementById('selectedDate');
    const currentDate = new Date(dateInput.value);
    currentDate.setDate(currentDate.getDate() + days);
    dateInput.value = currentDate.toISOString().split('T')[0];
    loadCalendar();
}

function setToday() {
    document.getElementById('selectedDate').value = new Date().toISOString().split('T')[0];
    loadCalendar();
}

// Tez booking qilish
function quickBook(time) {
    openAddBookingModal(time);
}

// ==========================================
// BOOKING DETAILS
// ==========================================

async function showBookingDetails(bookingId) {
    try {
        const response = await fetch(`../../backend/bookings/read.php?id=${bookingId}`);
        const result = await response.json();

        if (result.success) {
            const booking = result.data.bookings[0];
            displayBookingDetails(booking);
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        showToast('Xatolik yuz berdi', 'error');
    }
}

function displayBookingDetails(booking) {
    const progressColor = getProgressColor(booking.package_used, booking.package_total);
    const progressText = `${booking.package_used}/${booking.package_total}`;
    const progressPercentage = Math.round((booking.package_used / booking.package_total) * 100);

    const content = `
        <div class="space-y-4">
            
            <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-600"></i>Mijoz ma'lumotlari
                </h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-600">Ism:</span>
                        <p class="font-semibold">${booking.customer_name}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Telefon:</span>
                        <p class="font-semibold">${booking.customer_phone}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-green-600"></i>Booking ma'lumotlari
                </h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-600">Sana:</span>
                        <p class="font-semibold">${booking.slot_date}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Vaqt:</span>
                        <p class="font-semibold">${booking.slot_time}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Status:</span>
                        <p class="font-semibold">${getStatusBadge(booking.status)}</p>
                    </div>
                    <div>
                        <span class="text-gray-600">Booking qilindi:</span>
                        <p class="font-semibold text-xs">${booking.booking_date}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-box mr-2 text-purple-600"></i>Paket ma'lumotlari
                </h4>
                <div class="space-y-3">
                    <div>
                        <span class="text-gray-600 text-sm">Paket hajmi:</span>
                        <p class="font-semibold">${booking.package_snapshot_total} ta reklama</p>
                    </div>
                    <div>
                        <span class="text-gray-600 text-sm">Foydalanish:</span>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="inline-block px-3 py-1 rounded font-bold text-sm ${progressColor}">
                                ${progressText}
                            </span>
                            <div class="flex-1">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-green-500 via-yellow-500 to-red-500 transition-all duration-300" 
                                         style="width: ${progressPercentage}%"></div>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-600">${progressPercentage}%</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-white rounded p-2">
                            <p class="text-gray-600">Jami</p>
                            <p class="font-bold text-lg">${booking.package_total}</p>
                        </div>
                        <div class="bg-white rounded p-2">
                            <p class="text-gray-600">Ishlatildi</p>
                            <p class="font-bold text-lg text-blue-600">${booking.package_used}</p>
                        </div>
                        <div class="bg-white rounded p-2">
                            <p class="text-gray-600">Qoldi</p>
                            <p class="font-bold text-lg text-green-600">${booking.package_remaining}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-indigo-50 rounded-lg p-4" id="customerPackageHistory">
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-history mr-2 text-indigo-600"></i>Paket tarixi
                </h4>
                <div class="text-center py-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500 mx-auto"></div>
                </div>
            </div>
            
            ${booking.ad_description && booking.ad_description !== '-' ? `
            <div class="bg-yellow-50 rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-2 flex items-center">
                    <i class="fas fa-comment mr-2 text-yellow-600"></i>Izoh
                </h4>
                <p class="text-gray-700">${booking.ad_description}</p>
            </div>
            ` : ''}
            
            <div class="flex gap-3 pt-4 border-t">
                <button onclick="closeBookingDetailsModal()" 
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Orqaga
                </button>
                <button onclick="openMoveBooking(${booking.id}, '${booking.slot_date}', '${booking.slot_time}')" 
                        class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition font-semibold">
                    <i class="fas fa-arrows-alt mr-2"></i>Ko'chirish
                </button>
                <button onclick="cancelBooking(${booking.id})" 
                        class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition font-semibold">
                    <i class="fas fa-times mr-2"></i>Bekor qilish
                </button>
            </div>
            
        </div>
    `;

    document.getElementById('bookingDetailsContent').innerHTML = content;
    document.getElementById('bookingDetailsModal').classList.remove('hidden');
    loadCustomerPackageHistory(booking.customer_id, booking.id);
}

async function loadCustomerPackageHistory(customerId, currentBookingId) {
    try {
        const response = await fetch(`../../backend/bookings/read.php?customer_id=${customerId}&limit=100`);
        const result = await response.json();

        if (result.success) {
            const allBookings = result.data.bookings;
            const container = document.getElementById('customerPackageHistory');

            if (allBookings.length === 0) {
                container.innerHTML = `
                    <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-history mr-2 text-indigo-600"></i>Paket tarixi
                    </h4>
                    <p class="text-gray-500 text-sm text-center py-2">Paket tarixi yo'q</p>
                `;
                return;
            }

            const historyHtml = allBookings.map(ad => {
                const adProgressColor = getProgressColor(ad.package_used, ad.package_total);
                const adProgressText = `${ad.package_used}/${ad.package_total}`;
                const isPast = new Date(ad.slot_date) < new Date();
                const isCurrent = ad.id == currentBookingId;

                let statusBadge = '';
                if (ad.status === 'cancelled') {
                    statusBadge = '<span class="text-xs text-red-500"><i class="fas fa-times-circle"></i> Bekor</span>';
                } else if (ad.status === 'published') {
                    statusBadge = '<span class="text-xs text-green-500"><i class="fas fa-check-circle"></i> Chiqdi</span>';
                } else if (isPast) {
                    statusBadge = '<span class="text-xs text-gray-400"><i class="fas fa-history"></i> O\'tgan</span>';
                }

                return `
                    <div class="bg-white rounded p-3 mb-2 hover:bg-gray-50 transition cursor-pointer ${isCurrent ? 'ring-2 ring-blue-400' : ''}" onclick="showBookingDetails(${ad.id})">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="inline-block px-2 py-1 rounded text-xs font-bold ${adProgressColor}">
                                    ${adProgressText}
                                </span>
                                ${isCurrent ? '<span class="text-xs font-bold text-blue-600"><i class="fas fa-star"></i> Joriy</span>' : ''}
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-700">${ad.slot_date}</span>
                                <span class="text-sm text-gray-600">${ad.slot_time}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-600 truncate flex-1">${(ad.ad_description && ad.ad_description !== '-') ? ad.ad_description : '<span class="text-gray-400 italic">Izohsiz</span>'}</p>
                            ${statusBadge}
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `
                <h4 class="font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-history mr-2 text-indigo-600"></i>Paket tarixi (${allBookings.length} ta)
                </h4>
                <div class="max-h-64 overflow-y-auto">${historyHtml}</div>
            `;
        }
    } catch (error) {
        console.error('Load package history error:', error);
    }
}

function getStatusBadge(status) {
    const badges = {
        'scheduled': '<span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">Rejalashtirilgan</span>',
        'published': '<span class="inline-block px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-semibold">Chop etilgan</span>',
        'cancelled': '<span class="inline-block px-2 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold">Bekor qilindi</span>'
    };
    return badges[status] || status;
}

// ==========================================
// BOOKING ACTIONS (Move, Cancel)
// ==========================================

function openMoveBooking(bookingId, currentDate, currentTime) {
    document.getElementById('moveBookingId').value = bookingId;
    document.getElementById('moveBookingDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('moveBookingTime').value = currentTime;
    document.getElementById('bookingDetailsModal').classList.add('hidden');
    document.getElementById('moveBookingModal').classList.remove('hidden');
}

document.getElementById('moveBookingForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const bookingId = document.getElementById('moveBookingId').value;
    const newDate = document.getElementById('moveBookingDate').value;
    const newTime = document.getElementById('moveBookingTime').value;

    try {
        showLoading();
        const formData = new FormData();
        formData.append('id', bookingId);
        formData.append('slot_date', newDate);
        formData.append('slot_time', newTime);

        const response = await fetch('../../backend/bookings/update.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Booking ko\'chirildi!', 'success');
            closeMoveBookingModal();
            loadCalendar();
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
});

async function cancelBooking(bookingId) {
    if (!confirm('Haqiqatan ham bu bookingni bekor qilmoqchimisiz?')) {
        return;
    }

    try {
        showLoading();
        const formData = new FormData();
        formData.append('id', bookingId);
        formData.append('status', 'cancelled');

        const response = await fetch('../../backend/bookings/update.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Booking bekor qilindi', 'success');
            closeBookingDetailsModal();
            loadCalendar();
        } else {
            showToast(result.error || 'Xatolik', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
}

// ==========================================
// MODAL CONTROLS
// ==========================================

function closeBookingDetailsModal() {
    document.getElementById('bookingDetailsModal').classList.add('hidden');
}

function closeMoveBookingModal() {
    document.getElementById('moveBookingModal').classList.add('hidden');
    document.getElementById('moveBookingForm').reset();
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('bookingForm').reset();
    clearCustomerSelection();
}

// ==========================================
// MIJOZ QIDIRISH (AUTOCOMPLETE)
// ==========================================

async function loadAllCustomers() {
    try {
        const response = await fetch('../../backend/customers/read.php?status=active&limit=1000');
        const result = await response.json();
        if (result.success) {
            allCustomers = result.data.customers;
        }
    } catch (error) {
        console.error('Mijozlar yuklanmadi:', error);
    }
}

function searchCustomers(query) {
    const resultsContainer = document.getElementById('customerResults');

    if (!query || query.length < 2) {
        resultsContainer.classList.add('hidden');
        return;
    }

    const queryLower = query.toLowerCase();
    const filtered = allCustomers.filter(c => {
        const name = (c.ad_name || '').toLowerCase();
        const contact = (c.contact_person || '').toLowerCase();
        const phone = (c.phone || '').replace(/\s/g, '');
        return name.includes(queryLower) ||
            contact.includes(queryLower) ||
            phone.includes(query.replace(/\s/g, ''));
    }).slice(0, 10);

    if (filtered.length === 0) {
        resultsContainer.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <i class="fas fa-search mb-2 text-2xl"></i>
                <p>"${query}" bo'yicha topilmadi</p>
                <button type="button" onclick="openQuickAddCustomerFromCalendar()" 
                        class="mt-2 text-green-600 hover:text-green-700 font-semibold">
                    <i class="fas fa-plus mr-1"></i>Yangi mijoz qo'shish
                </button>
            </div>
        `;
        resultsContainer.classList.remove('hidden');
        return;
    }

    resultsContainer.innerHTML = filtered.map(c => `
        <div class="p-3 hover:bg-blue-50 cursor-pointer border-b last:border-b-0 transition" 
             onclick="selectCustomer(${c.id}, '${escapeHtml(c.ad_name)}', '${escapeHtml(c.phone)}')">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-gray-800">${highlightMatch(c.ad_name, query)}</p>
                    ${c.contact_person ? `<p class="text-xs text-gray-500">${c.contact_person}</p>` : ''}
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-600">${c.phone}</p>
                    ${c.total_remaining_ads > 0 ?
            `<p class="text-xs text-green-600"><i class="fas fa-box"></i> ${c.total_remaining_ads} ta qolgan</p>` :
            `<p class="text-xs text-red-500"><i class="fas fa-exclamation-circle"></i> Paket yo'q</p>`
        }
                </div>
            </div>
        </div>
    `).join('');

    resultsContainer.classList.remove('hidden');
}

function highlightMatch(text, query) {
    if (!query || !text) return text || '';
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<span class="bg-yellow-200 font-bold">$1</span>');
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function selectCustomer(id, name, phone) {
    document.getElementById('bookingCustomer').value = id;
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerResults').classList.add('hidden');

    document.getElementById('selectedCustomerName').textContent = name;
    document.getElementById('selectedCustomerPhone').textContent = phone;
    document.getElementById('selectedCustomerInfo').classList.remove('hidden');

    loadCustomerPackages();
}

function clearCustomerSelection() {
    document.getElementById('bookingCustomer').value = '';
    document.getElementById('customerSearch').value = '';
    document.getElementById('selectedCustomerInfo').classList.add('hidden');
    document.getElementById('customerResults').classList.add('hidden');
    document.getElementById('bookingPackage').innerHTML = '<option value="">Avval mijozni tanlang...</option>';
    document.getElementById('packageInfo').textContent = '';
}

// ==========================================
// BOOKING MODAL
// ==========================================

async function openAddBookingModal(preselectedTime = null) {
    await loadAllCustomers();
    clearCustomerSelection();
    document.getElementById('bookingForm').reset();

    // Sana o'rnatish
    document.getElementById('bookingDate').value = document.getElementById('selectedDate').value;

    // Vaqt o'rnatish (agar tanlangan bo'lsa)
    if (preselectedTime) {
        document.getElementById('bookingTime').value = preselectedTime;
    }

    document.getElementById('bookingModal').classList.remove('hidden');
}

async function loadCustomerPackages() {
    const customerId = document.getElementById('bookingCustomer').value;
    const packageSelect = document.getElementById('bookingPackage');
    const infoText = document.getElementById('packageInfo');

    if (!customerId) {
        packageSelect.innerHTML = '<option value="">Avval mijozni tanlang...</option>';
        infoText.textContent = '';
        return;
    }

    try {
        const response = await fetch(`../../backend/customers/get_by_id.php?id=${customerId}`);
        const result = await response.json();

        if (result.success) {
            const activePackages = result.data.packages.filter(p => p.status === 'active' && p.remaining_ads > 0);
            const customerName = document.getElementById('selectedCustomerName').textContent;

            if (activePackages.length === 0) {
                packageSelect.innerHTML = '<option value="">Bu mijozda aktiv paket yo\'q</option>';
                infoText.innerHTML = `
                    <span class="text-red-500">Avval paket biriktiring.</span>
                    <button type="button" onclick="openQuickPackageModal(${customerId}, '${escapeHtml(customerName)}')" 
                            class="ml-2 text-purple-600 hover:text-purple-700 font-semibold">
                        <i class="fas fa-plus mr-1"></i>Paket qo'shish
                    </button>
                `;
                return;
            }

            packageSelect.innerHTML = '<option value="">Paketni tanlang...</option>' +
                activePackages.map(p => {
                    const progressText = `${p.used_ads}/${p.total_ads}`;
                    return `<option value="${p.id}" data-remaining="${p.remaining_ads}" data-total="${p.total_ads}" data-used="${p.used_ads}">
                        ${p.total_ads} ta reklama - ${progressText} (${p.remaining_ads} qolgan)
                    </option>`;
                }).join('');

            infoText.innerHTML = `
                <span class="text-green-600">${activePackages.length} ta aktiv paket</span>
                <button type="button" onclick="openQuickPackageModal(${customerId}, '${escapeHtml(customerName)}')" 
                        class="ml-2 text-purple-600 hover:text-purple-700 font-semibold text-xs">
                    <i class="fas fa-plus mr-1"></i>Yana qo'shish
                </button>
            `;
        }
    } catch (error) {
        showToast('Paketlarni yuklashda xatolik', 'error');
        console.error(error);
    }
}

// Booking Form submit
document.getElementById('bookingForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        showLoading();
        const response = await fetch('../../backend/bookings/create.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Booking muvaffaqiyatli!', 'success');
            closeBookingModal();
            loadCalendar();
        } else {
            showToast(result.error || 'Xatolik', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
    }
});

// ==========================================
// QUICK ADD CUSTOMER
// ==========================================

function openQuickAddCustomerFromCalendar() {
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
    formData.append('contact_person', document.getElementById('quickCustomerContact')?.value || '');
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
            await loadAllCustomers();
            selectCustomer(result.data.id, result.data.ad_name, result.data.phone);
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
        console.error(error);
    }
});

// ==========================================
// QUICK ADD PACKAGE
// ==========================================

let availablePackages = [];

// ==========================================
// QUICK ADD PACKAGE (YANGILANGAN)
// ==========================================

function openQuickPackageModal(customerId, customerName) {
    document.getElementById('quickPackageCustomerId').value = customerId;
    document.getElementById('quickPackageCustomerName').textContent = customerName;

    // Formani tozalash
    document.getElementById('quickPackageAdsCount').value = '';
    document.getElementById('quickPackagePaymentType').value = 'naqd';
    document.getElementById('quickPackagePaymentAmount').value = '';
    document.getElementById('quickPackageNotes').value = '';

    // To'lov summasini ko'rsatish
    togglePaymentAmount();

    document.getElementById('quickPackageModal').classList.remove('hidden');
}

function closeQuickPackageModal() {
    document.getElementById('quickPackageModal').classList.add('hidden');
}

// To'lov turi o'zgarganda summa fieldini ko'rsatish/yashirish
function togglePaymentAmount() {
    const paymentType = document.getElementById('quickPackagePaymentType').value;
    const amountContainer = document.getElementById('paymentAmountContainer');
    const amountInput = document.getElementById('quickPackagePaymentAmount');

    if (paymentType === 'bepul') {
        amountContainer.style.display = 'none';
        amountInput.removeAttribute('required');
        amountInput.value = '0';
    } else {
        amountContainer.style.display = 'block';
        amountInput.setAttribute('required', 'required');
        if (amountInput.value === '0') {
            amountInput.value = '';
        }
    }
}

document.getElementById('quickPackageForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const customerId = document.getElementById('quickPackageCustomerId').value;
    const adsCount = document.getElementById('quickPackageAdsCount').value;
    const paymentType = document.getElementById('quickPackagePaymentType').value;
    const paymentAmount = document.getElementById('quickPackagePaymentAmount').value || 0;
    const notes = document.getElementById('quickPackageNotes').value;

    if (!adsCount || adsCount < 1) {
        showToast('Reklama sonini kiriting', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('customer_id', customerId);
    formData.append('total_ads', adsCount);
    formData.append('payment_method', paymentType);
    formData.append('amount', paymentAmount);
    formData.append('notes', notes);

    try {
        showLoading();
        const response = await fetch('../../backend/packages/create_with_payment.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        hideLoading();

        if (result.success) {
            showToast('Paket yaratildi va to\'lov qabul qilindi!', 'success');
            closeQuickPackageModal();
            loadCustomerPackages();
        } else {
            showToast(result.error, 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Xatolik yuz berdi', 'error');
        console.error(error);
    }
});

// ==========================================
// SEARCH IN CALENDAR
// ==========================================

function searchBookings() {
    const searchTerm = document.getElementById('calendarSearch').value.toLowerCase();

    if (!searchTerm) {
        renderCalendar({ time_slots: allBookingsData, statistics: {} });
        return;
    }

    const filtered = allBookingsData.filter(slot => {
        if (!slot.is_booked) return false;
        const customerName = (slot.booking.customer_name || '').toLowerCase();
        const description = (slot.booking.ad_description || '').toLowerCase();
        const phone = (slot.booking.customer_phone || '').toLowerCase();
        return customerName.includes(searchTerm) || description.includes(searchTerm) || phone.includes(searchTerm);
    });

    if (filtered.length === 0) {
        document.getElementById('timeSlotsGrid').innerHTML = `
            <div class="col-span-full text-center py-12 text-gray-500">
                <i class="fas fa-search text-5xl mb-3 opacity-50"></i>
                <p class="text-lg">Topilmadi: "${searchTerm}"</p>
                <button onclick="document.getElementById('calendarSearch').value=''; searchBookings();" class="mt-4 text-blue-600 hover:text-blue-700">Tozalash</button>
            </div>
        `;
    } else {
        renderCalendar({ time_slots: filtered, statistics: {} });
    }
}

// ==========================================
// INITIALIZATION
// ==========================================

document.addEventListener('DOMContentLoaded', function () {
    loadCalendar();
    loadAllCustomers();

    // Customer search input
    const searchInput = document.getElementById('customerSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => searchCustomers(this.value), 300);
        });

        searchInput.addEventListener('focus', function () {
            if (this.value.length >= 2) searchCustomers(this.value);
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('#customerSearch') && !e.target.closest('#customerResults')) {
                document.getElementById('customerResults').classList.add('hidden');
            }
        });
    }
});

console.log('CALENDAR.JS LOADED');