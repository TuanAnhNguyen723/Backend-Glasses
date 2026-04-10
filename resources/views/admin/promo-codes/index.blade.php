@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Mã Giảm Giá')
@section('page-title', 'Mã Giảm Giá')
@section('page-description', 'Quản lý mã giảm giá cho toàn bộ sản phẩm hoặc từng sản phẩm')

@section('header-actions')
    <button id="add-promo-btn" class="bg-primary text-white flex items-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all">
        <span class="material-symbols-outlined text-xl">add</span>
        <span>Thêm mã giảm giá</span>
    </button>
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 border border-[#e7edf3] dark:border-slate-800 rounded-2xl p-5 flex flex-wrap items-center gap-3">
        <div class="relative w-full md:w-72">
            <span class="material-symbols-outlined absolute left-3 top-2.5 text-[#4c739a]">search</span>
            <input id="search-input" type="text" placeholder="Tìm mã hoặc tên..." class="w-full pl-10 pr-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950 text-sm">
        </div>
        <select id="scope-filter" class="px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950 text-sm">
            <option value="">Tất cả phạm vi</option>
            <option value="all_products">Toàn bộ sản phẩm</option>
            <option value="product">Theo sản phẩm</option>
        </select>
        <select id="status-filter" class="px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950 text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="active">Đang bật</option>
            <option value="inactive">Đang tắt</option>
        </select>
    </div>

    <div class="bg-white dark:bg-slate-900 border border-[#e7edf3] dark:border-slate-800 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f8fbff] dark:bg-slate-800/60 text-[#4c739a]">
                    <tr>
                        <th class="text-left px-5 py-3">Mã</th>
                        <th class="text-left px-5 py-3">Phạm vi</th>
                        <th class="text-left px-5 py-3">Giảm giá</th>
                        <th class="text-left px-5 py-3">Sử dụng</th>
                        <th class="text-left px-5 py-3">Hiệu lực</th>
                        <th class="text-left px-5 py-3">Trạng thái</th>
                        <th class="text-right px-5 py-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="promo-table-body">
                    <tr><td class="px-5 py-6 text-center text-[#4c739a]" colspan="7">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-[#e7edf3] dark:border-slate-800 flex items-center justify-between">
            <p id="pagination-info" class="text-sm text-[#4c739a]">Hiển thị 0 - 0 / 0</p>
            <div id="pagination-controls" class="flex items-center gap-2"></div>
        </div>
    </div>

    <div id="promo-modal" class="hidden fixed inset-0 z-[200] bg-black/45 backdrop-blur-sm items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-[#e7edf3] dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-2xl">
            <div class="px-6 py-4 border-b border-[#e7edf3] dark:border-slate-800 flex items-center justify-between">
                <h3 id="promo-modal-title" class="text-lg font-bold">Thêm mã giảm giá</h3>
                <button id="close-modal-btn" class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="promo-form" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="hidden" id="promo-id">
                <div>
                    <label class="block text-sm font-semibold mb-1">Mã giảm giá *</label>
                    <input id="code" required class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tên hiển thị</label>
                    <input id="name" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Phạm vi áp dụng *</label>
                    <select id="scope" required class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950">
                        <option value="all_products">Toàn bộ sản phẩm</option>
                        <option value="product">Theo sản phẩm</option>
                    </select>
                </div>
                <div id="product-wrapper" class="hidden">
                    <label class="block text-sm font-semibold mb-1">Sản phẩm áp dụng *</label>
                    <select id="product_id" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950">
                        <option value="">-- Chọn sản phẩm --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Loại giảm *</label>
                    <select id="discount_type" required class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950">
                        <option value="percent">Theo phần trăm (%)</option>
                        <option value="fixed">Giảm cố định (VND)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Giá trị giảm *</label>
                    <input id="discount_value" type="number" min="0.01" step="0.01" required class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Đơn tối thiểu</label>
                    <input id="min_order_amount" type="number" min="0" step="0.01" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Giảm tối đa</label>
                    <input id="max_discount_amount" type="number" min="0" step="0.01" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Giới hạn lượt dùng</label>
                    <input id="usage_limit" type="number" min="1" step="1" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                </div>
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1">Bắt đầu</label>
                        <input id="starts_at" type="datetime-local" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1">Kết thúc</label>
                        <input id="ends_at" type="datetime-local" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"/>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold mb-1">Mô tả</label>
                    <textarea id="description" rows="3" class="w-full px-3 py-2.5 rounded-xl border border-[#dbe4ee] dark:border-slate-700 bg-white dark:bg-slate-950"></textarea>
                </div>
                <div class="md:col-span-2 flex items-center justify-between">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input id="is_active" type="checkbox" checked>
                        <span>Kích hoạt ngay</span>
                    </label>
                    <button type="submit" class="bg-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm">Lưu mã giảm giá</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentPage = 1;
let products = [];
let promoMap = {};
const state = { search: '', scope: '', status: '' };

async function loadProducts() {
    const res = await fetch(`{{ route('admin.api.promo-codes.products') }}`);
    const json = await res.json();
    products = json.data || [];
    const select = document.getElementById('product_id');
    const options = ['<option value="">-- Chọn sản phẩm --</option>']
        .concat(products.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`));
    select.innerHTML = options.join('');
}

async function loadPromoCodes(page = 1) {
    const params = new URLSearchParams({ page, per_page: 10 });
    if (state.search) params.append('search', state.search);
    if (state.scope) params.append('scope', state.scope);
    if (state.status) params.append('status', state.status);
    const res = await fetch(`{{ route('admin.api.promo-codes') }}?${params.toString()}`);
    const json = await res.json();
    renderTable(json.data || []);
    renderPagination(json);
    currentPage = page;
}

function renderTable(rows) {
    const body = document.getElementById('promo-table-body');
    promoMap = {};
    if (!rows.length) {
        body.innerHTML = '<tr><td class="px-5 py-6 text-center text-[#4c739a]" colspan="7">Không có mã giảm giá</td></tr>';
        return;
    }
    body.innerHTML = rows.map(row => {
        promoMap[row.id] = row;
        const scopeText = row.scope === 'product'
            ? `Sản phẩm: ${escapeHtml(row.product?.name || '-')}`
            : 'Toàn bộ sản phẩm';
        const discountText = row.discount_type === 'percent'
            ? `${Number(row.discount_value)}%`
            : `${Number(row.discount_value).toLocaleString('vi-VN')}đ`;
        const usage = `${row.used_count}${row.usage_limit ? ' / ' + row.usage_limit : ''}`;
        const windowText = `${formatDate(row.starts_at) || '-'} → ${formatDate(row.ends_at) || '-'}`;
        return `
            <tr class="border-t border-[#eef2f7] dark:border-slate-800">
                <td class="px-5 py-3">
                    <p class="font-bold">${escapeHtml(row.code)}</p>
                    <p class="text-xs text-[#4c739a]">${escapeHtml(row.name || '')}</p>
                </td>
                <td class="px-5 py-3">${scopeText}</td>
                <td class="px-5 py-3">
                    <p>${discountText}</p>
                    <p class="text-xs text-[#4c739a]">Min: ${Number(row.min_order_amount || 0).toLocaleString('vi-VN')}đ</p>
                </td>
                <td class="px-5 py-3">${usage}</td>
                <td class="px-5 py-3">${windowText}</td>
                <td class="px-5 py-3">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold ${row.is_active ? 'text-green-600' : 'text-slate-500'}">
                        <input type="checkbox" ${row.is_active ? 'checked' : ''} onchange="toggleStatus(${row.id}, this.checked)">
                        <span>${row.is_active ? 'Đang bật' : 'Đang tắt'}</span>
                    </label>
                </td>
                <td class="px-5 py-3 text-right">
                    <button onclick="openEditModal(${row.id})" class="px-3 py-1.5 rounded-lg text-xs bg-slate-100 dark:bg-slate-800">Sửa</button>
                    <button onclick="removePromo(${row.id})" class="px-3 py-1.5 rounded-lg text-xs bg-red-100 text-red-700 ml-2">Xóa</button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(meta) {
    document.getElementById('pagination-info').textContent = `Hiển thị ${meta.from || 0} - ${meta.to || 0} / ${meta.total || 0}`;
    const controls = document.getElementById('pagination-controls');
    if ((meta.last_page || 1) <= 1) { controls.innerHTML = ''; return; }
    controls.innerHTML = `
        <button class="px-3 py-1 rounded-lg border" ${meta.current_page <= 1 ? 'disabled' : ''} onclick="loadPromoCodes(${meta.current_page - 1})">Trước</button>
        <span class="px-2 text-sm">${meta.current_page} / ${meta.last_page}</span>
        <button class="px-3 py-1 rounded-lg border" ${meta.current_page >= meta.last_page ? 'disabled' : ''} onclick="loadPromoCodes(${meta.current_page + 1})">Sau</button>
    `;
}

function openCreateModal() {
    document.getElementById('promo-modal-title').textContent = 'Thêm mã giảm giá';
    document.getElementById('promo-form').reset();
    document.getElementById('promo-id').value = '';
    toggleProductField();
    showModal();
}

function openEditModal(id) {
    const row = promoMap[id];
    if (!row) return;
    document.getElementById('promo-modal-title').textContent = 'Cập nhật mã giảm giá';
    document.getElementById('promo-id').value = row.id;
    document.getElementById('code').value = row.code || '';
    document.getElementById('name').value = row.name || '';
    document.getElementById('scope').value = row.scope || 'all_products';
    document.getElementById('discount_type').value = row.discount_type || 'percent';
    document.getElementById('discount_value').value = row.discount_value || '';
    document.getElementById('min_order_amount').value = row.min_order_amount || '';
    document.getElementById('max_discount_amount').value = row.max_discount_amount || '';
    document.getElementById('usage_limit').value = row.usage_limit || '';
    document.getElementById('starts_at').value = toDatetimeLocal(row.starts_at);
    document.getElementById('ends_at').value = toDatetimeLocal(row.ends_at);
    document.getElementById('description').value = row.description || '';
    document.getElementById('is_active').checked = !!row.is_active;
    toggleProductField();
    document.getElementById('product_id').value = row.product_id || '';
    showModal();
}

function showModal() {
    const modal = document.getElementById('promo-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('promo-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function toggleProductField() {
    const isProductScope = document.getElementById('scope').value === 'product';
    const wrapper = document.getElementById('product-wrapper');
    wrapper.classList.toggle('hidden', !isProductScope);
}

async function submitForm(e) {
    e.preventDefault();
    const id = document.getElementById('promo-id').value;
    const payload = {
        code: document.getElementById('code').value.trim(),
        name: document.getElementById('name').value.trim() || null,
        scope: document.getElementById('scope').value,
        product_id: document.getElementById('product_id').value || null,
        discount_type: document.getElementById('discount_type').value,
        discount_value: document.getElementById('discount_value').value,
        min_order_amount: document.getElementById('min_order_amount').value || 0,
        max_discount_amount: document.getElementById('max_discount_amount').value || null,
        usage_limit: document.getElementById('usage_limit').value || null,
        starts_at: document.getElementById('starts_at').value || null,
        ends_at: document.getElementById('ends_at').value || null,
        description: document.getElementById('description').value.trim() || null,
        is_active: document.getElementById('is_active').checked,
    };

    const isUpdate = !!id;
    const url = isUpdate
        ? `{{ route('admin.api.promo-codes.update', ':id') }}`.replace(':id', id)
        : `{{ route('admin.api.promo-codes.store') }}`;
    const method = isUpdate ? 'PUT' : 'POST';
    const res = await fetch(url, {
        method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (!res.ok) {
        notificationManager.error(json.message || 'Không thể lưu mã giảm giá', 'Lỗi');
        return;
    }
    notificationManager.success(json.message || 'Đã lưu', 'Thành công');
    closeModal();
    loadPromoCodes(currentPage);
}

async function toggleStatus(id, isActive) {
    const res = await fetch(`{{ route('admin.api.promo-codes.toggle-status', ':id') }}`.replace(':id', id), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_active: isActive })
    });
    const json = await res.json();
    if (!res.ok) {
        notificationManager.error(json.message || 'Không thể cập nhật trạng thái', 'Lỗi');
        loadPromoCodes(currentPage);
        return;
    }
    notificationManager.success('Đã cập nhật trạng thái', 'Thành công');
}

async function removePromo(id) {
    if (!confirm('Bạn có chắc muốn xóa mã giảm giá này?')) return;
    const res = await fetch(`{{ route('admin.api.promo-codes.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    });
    const json = await res.json();
    if (!res.ok) {
        notificationManager.error(json.message || 'Không thể xóa', 'Lỗi');
        return;
    }
    notificationManager.success('Đã xóa mã giảm giá', 'Thành công');
    loadPromoCodes(currentPage);
}

function toDatetimeLocal(value) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function formatDate(value) {
    if (!value) return '';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return '';
    return d.toLocaleString('vi-VN');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
}

let searchTimeout;
document.getElementById('search-input').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        state.search = e.target.value.trim();
        loadPromoCodes(1);
    }, 350);
});
document.getElementById('scope-filter').addEventListener('change', (e) => {
    state.scope = e.target.value;
    loadPromoCodes(1);
});
document.getElementById('status-filter').addEventListener('change', (e) => {
    state.status = e.target.value;
    loadPromoCodes(1);
});
document.getElementById('scope').addEventListener('change', toggleProductField);
document.getElementById('add-promo-btn').addEventListener('click', openCreateModal);
document.getElementById('close-modal-btn').addEventListener('click', closeModal);
document.getElementById('promo-form').addEventListener('submit', submitForm);
document.getElementById('promo-modal').addEventListener('click', (e) => {
    if (e.target.id === 'promo-modal') closeModal();
});

document.addEventListener('DOMContentLoaded', async () => {
    await loadProducts();
    await loadPromoCodes();
});
</script>
@endpush
