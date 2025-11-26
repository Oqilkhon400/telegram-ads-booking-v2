let customers = [];

async function loadCustomers() {
    const tbody = document.getElementById('customersTableBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8">Yuklanmoqda...</td></tr>';

    try {
        const response = await fetch('../../backend/customers/read.php?page=1&limit=100');
        const result = await response.json();

        if (result.success) {
            customers = result.data.customers;
            renderTable();
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-red-600">Xatolik: ' + result.error + '</td></tr>';
        }
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-red-600">Xatolik: ' + error.message + '</td></tr>';
    }
}

function renderTable() {
    const tbody = document.getElementById('customersTableBody');

    if (customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8">Mijozlar yo\'q</td></tr>';
        return;
    }

    tbody.innerHTML = customers.map(c => `
        <tr>
            <td class="px-6 py-4">${c.id}</td>
            <td class="px-6 py-4">
                <div class="font-semibold">${c.ad_name}</div>
                <div class="text-sm text-gray-500">${c.phone}</div>
            </td>
            <td class="px-6 py-4">${c.address || '-'}</td>
            <td class="px-6 py-4">0 ta</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Faol</span>
            </td>
            <td class="px-6 py-4 text-right">
                <button onclick="editCustomer(${c.id})" class="text-blue-600 hover:text-blue-800 mr-2">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteCustomer(${c.id})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openAddCustomerModal() {
    document.getElementById('customerModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Yangi Mijoz';
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
}

function closeCustomerModal() {
    document.getElementById('customerModal').classList.add('hidden');
}

async function saveCustomer() {
    const form = document.getElementById('customerForm');
    const data = new FormData(form);

    const id = document.getElementById('customerId').value;
    const url = id ? '../../backend/customers/update.php' : '../../backend/customers/create.php';

    try {
        const response = await fetch(url, { method: 'POST', body: data });
        const result = await response.json();

        if (result.success) {
            alert(id ? 'Yangilandi!' : 'Qo\'shildi!');
            closeCustomerModal();
            loadCustomers();
        } else {
            alert('Xatolik: ' + result.error);
        }
    } catch (error) {
        alert('Xatolik: ' + error.message);
    }
}

async function editCustomer(id) {
    try {
        const response = await fetch('../../backend/customers/get_by_id.php?id=' + id);
        const result = await response.json();

        if (result.success) {
            const c = result.data;
            document.getElementById('customerId').value = c.id;
            document.getElementById('ad_name').value = c.ad_name;
            document.getElementById('phone').value = c.phone;
            document.getElementById('address').value = c.address || '';
            document.getElementById('is_active').checked = c.is_active == 1;

            document.getElementById('modalTitle').textContent = 'Tahrirlash';
            document.getElementById('customerModal').classList.remove('hidden');
        }
    } catch (error) {
        alert('Xatolik: ' + error.message);
    }
}

async function deleteCustomer(id) {
    if (!confirm('O\'chirmoqchimisiz?')) return;

    try {
        const data = new FormData();
        data.append('id', id);

        const response = await fetch('../../backend/customers/delete.php', { method: 'POST', body: data });
        const result = await response.json();

        if (result.success) {
            alert('O\'chirildi!');
            loadCustomers();
        } else {
            alert('Xatolik: ' + result.error);
        }
    } catch (error) {
        alert('Xatolik: ' + error.message);
    }
}

function searchCustomers() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    if (!search) {
        renderTable();
        return;
    }

    const filtered = customers.filter(c =>
        c.ad_name.toLowerCase().includes(search) ||
        c.phone.includes(search)
    );

    const tbody = document.getElementById('customersTableBody');
    tbody.innerHTML = filtered.map(c => `
        <tr>
            <td class="px-6 py-4">${c.id}</td>
            <td class="px-6 py-4">
                <div class="font-semibold">${c.ad_name}</div>
                <div class="text-sm text-gray-500">${c.phone}</div>
            </td>
            <td class="px-6 py-4">${c.address || '-'}</td>
            <td class="px-6 py-4">0 ta</td>
            <td class="px-6 py-4">
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs">Faol</span>
            </td>
            <td class="px-6 py-4 text-right">
                <button onclick="editCustomer(${c.id})" class="text-blue-600 mr-2">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="deleteCustomer(${c.id})" class="text-red-600">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Load on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadCustomers);
} else {
    loadCustomers();
}