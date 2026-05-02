@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Quản Lý Lens')

@section('header')
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-8 py-6 flex flex-wrap items-center justify-between gap-6 border-b border-[#cfdbe7] dark:border-slate-800">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight dark:text-white">Quản Lý Lens</h2>
            <p class="text-[#4c739a] text-sm font-medium">Quản lý danh mục tròng kính độc lập với sản phẩm gọng.</p>
        </div>
        <button id="open-create-lens-modal" class="flex items-center gap-2 h-10 px-5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
            <span class="material-symbols-outlined text-lg">add</span>
            Thêm Lens
        </button>
    </header>
@endsection

@section('content')
    <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm mb-4">
        <label class="relative flex items-center">
            <span class="material-symbols-outlined absolute left-4 text-[#4c739a]">search</span>
            <input id="search-input" class="w-full pl-12 pr-4 py-2.5 rounded-xl border-none bg-[#f6f7f8] dark:bg-slate-800 text-[#0d141b] dark:text-white placeholder:text-[#4c739a] focus:ring-2 focus:ring-primary/50 text-sm font-medium" placeholder="Tìm theo tên lens, SKU..." type="text"/>
        </label>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f6f7f8]/50 dark:bg-slate-800/50 border-b border-[#cfdbe7] dark:border-slate-800">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">SKU</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Tên Lens</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Loại</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Giá</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Tồn kho</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Độ mắt</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Trạng thái</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-[#4c739a]">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="lenses-table-body" class="divide-y divide-[#cfdbe7] dark:divide-slate-800">
                    <tr><td colspan="8" class="px-6 py-8 text-center text-[#4c739a]">Đang tải dữ liệu...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-[#f6f7f8]/50 dark:bg-slate-800/50 border-t border-[#cfdbe7] dark:border-slate-800 flex items-center justify-between">
            <span id="pagination-info" class="text-xs font-medium text-[#4c739a]">Đang tải...</span>
            <div id="pagination-controls" class="flex items-center gap-1"></div>
        </div>
    </div>

    <div id="lens-modal" class="hidden fixed inset-0 z-[200] items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-xl w-full mx-4 border border-[#cfdbe7] dark:border-slate-800">
            <div class="p-5 border-b border-[#cfdbe7] dark:border-slate-800">
                <h3 id="lens-modal-title" class="text-lg font-bold text-[#0d141b] dark:text-white">Thêm Lens</h3>
            </div>
            <form id="lens-form" class="p-5 space-y-3">
                <input type="hidden" id="lens-id">
                <input id="lens-sku" class="w-full rounded-lg border-[#cfdbe7] bg-white text-sm py-2 px-3" placeholder="SKU" required>
                <input id="lens-name" class="w-full rounded-lg border-[#cfdbe7] bg-white text-sm py-2 px-3" placeholder="Tên lens" required>
                <select id="lens-type" class="w-full rounded-lg border-[#cfdbe7] bg-white text-sm py-2 px-3" required>
                    <option value="">Chọn loại lens...</option>
                    <option value="myopia">Lens cận</option>
                    <option value="hyperopia">Lens viễn</option>
                    <option value="blue_light">Lens chống ánh sáng xanh</option>
                    <option value="photochromic">Lens đổi màu</option>
                    <option value="progressive">Lens đa tròng</option>
                    <option value="sunglasses">Lens kính mát</option>
                </select>
                <input id="lens-price" type="number" min="0" step="1000" class="w-full rounded-lg border-[#cfdbe7] bg-white text-sm py-2 px-3" placeholder="Giá" required>
                <input id="lens-stock" type="number" min="0" class="w-full rounded-lg border-[#cfdbe7] bg-white text-sm py-2 px-3" placeholder="Tồn kho" required>
                <textarea id="lens-description" class="w-full rounded-lg border-[#cfdbe7] bg-white text-sm py-2 px-3" placeholder="Mô tả"></textarea>
                <label class="flex items-center gap-2 text-sm">
                    <input id="lens-requires-prescription" type="checkbox" checked class="rounded border-[#cfdbe7] text-primary">
                    Bắt buộc khách nhập độ mắt khi chọn lens này
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input id="lens-active" type="checkbox" checked class="rounded border-[#cfdbe7] text-primary">
                    Hoạt động
                </label>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" id="close-lens-modal" class="h-9 rounded-lg border border-[#cfdbe7] bg-white px-3 text-sm font-semibold">Hủy</button>
                    <button type="submit" class="h-9 rounded-lg bg-primary px-3 text-sm font-semibold text-white">Lưu</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let currentSearch = '';
    let currentPage = 1;
    let lenses = [];
    const lensTypeLabels = {
        myopia: 'Lens cận',
        hyperopia: 'Lens viễn',
        blue_light: 'Lens chống ánh sáng xanh',
        photochromic: 'Lens đổi màu',
        progressive: 'Lens đa tròng',
        sunglasses: 'Lens kính mát',
    };

    function formatCurrency(v) {
        return new Intl.NumberFormat('vi-VN').format(v) + ' đ';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function renderRows(items) {
        const tbody = document.getElementById('lenses-table-body');
        if (!items.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-6 py-8 text-center text-[#4c739a]">Không có dữ liệu lens</td></tr>';
            return;
        }
        tbody.innerHTML = items.map((lens) => `
            <tr class="hover:bg-[#f6f7f8] dark:hover:bg-slate-800/30 transition-colors group">
                <td class="px-6 py-4 text-sm font-semibold">${lens.sku}</td>
                <td class="px-6 py-4 text-sm">${lens.name}</td>
                <td class="px-6 py-4 text-sm">${lens.lens_type_label || lensTypeLabels[lens.lens_type] || lens.lens_type}</td>
                <td class="px-6 py-4 text-sm font-bold">${formatCurrency(lens.base_price)}</td>
                <td class="px-6 py-4 text-sm">${lens.stock_quantity}</td>
                <td class="px-6 py-4 text-sm">${lens.requires_prescription ? 'Bắt buộc' : 'Không bắt buộc'}</td>
                <td class="px-6 py-4 text-sm">${lens.is_active ? 'Hoạt động' : 'Tạm ẩn'}</td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="openEditModal(${lens.id})" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-lg text-[#4c739a] hover:text-primary border border-transparent hover:border-[#cfdbe7] transition-all" title="Sửa lens">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </button>
                        <button onclick="deleteLens(${lens.id})" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-lg text-[#4c739a] hover:text-red-500 border border-transparent hover:border-[#cfdbe7] transition-all" title="Xóa lens">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(meta) {
        const info = document.getElementById('pagination-info');
        const controls = document.getElementById('pagination-controls');

        info.textContent = `Hiển thị ${meta.from || 0}-${meta.to || 0} trong tổng số ${meta.total || 0} lens`;

        if (!meta.last_page || meta.last_page <= 1) {
            controls.innerHTML = '';
            return;
        }

        const current = Number(meta.current_page || 1);
        const last = Number(meta.last_page || 1);
        const start = Math.max(1, current - 2);
        const end = Math.min(last, start + 4);
        let html = `
            <button onclick="loadLenses(${current - 1})"
                    ${current === 1 ? 'disabled' : ''}
                    class="size-8 flex items-center justify-center rounded-lg border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-[#4c739a] hover:text-primary transition-colors disabled:opacity-50">
                <span class="material-symbols-outlined text-sm">chevron_left</span>
            </button>
        `;

        for (let page = start; page <= end; page++) {
            const isActive = page === current;
            html += `
                <button onclick="loadLenses(${page})"
                        class="size-8 flex items-center justify-center rounded-lg border ${isActive ? 'border-primary bg-primary text-white' : 'border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-[#4c739a] hover:text-primary'} text-xs font-bold transition-colors">
                    ${page}
                </button>
            `;
        }

        html += `
            <button onclick="loadLenses(${current + 1})"
                    ${current === last ? 'disabled' : ''}
                    class="size-8 flex items-center justify-center rounded-lg border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-[#4c739a] hover:text-primary transition-colors disabled:opacity-50">
                <span class="material-symbols-outlined text-sm">chevron_right</span>
            </button>
        `;
        controls.innerHTML = html;
    }

    async function loadLenses(page = 1) {
        const params = new URLSearchParams();
        params.append('page', page);
        if (currentSearch) params.append('search', currentSearch);
        const res = await fetch(`{{ route('admin.api.lenses') }}?${params.toString()}`);
        const json = await res.json();
        lenses = json.data || [];
        currentPage = json.current_page || page;
        renderRows(lenses);
        renderPagination(json);
    }

    function openModal() {
        document.getElementById('lens-modal').classList.remove('hidden');
        document.getElementById('lens-modal').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('lens-modal').classList.add('hidden');
        document.getElementById('lens-modal').classList.remove('flex');
    }

    function resetForm() {
        document.getElementById('lens-id').value = '';
        document.getElementById('lens-form').reset();
        document.getElementById('lens-requires-prescription').checked = true;
        document.getElementById('lens-active').checked = true;
    }

    function openEditModal(id) {
        const lens = lenses.find((item) => item.id === id);
        if (!lens) return;
        document.getElementById('lens-modal-title').textContent = 'Cập nhật Lens';
        document.getElementById('lens-id').value = lens.id;
        document.getElementById('lens-sku').value = lens.sku;
        document.getElementById('lens-name').value = lens.name;
        document.getElementById('lens-type').value = lens.lens_type;
        document.getElementById('lens-price').value = lens.base_price;
        document.getElementById('lens-stock').value = lens.stock_quantity;
        document.getElementById('lens-description').value = lens.description || '';
        document.getElementById('lens-requires-prescription').checked = !!lens.requires_prescription;
        document.getElementById('lens-active').checked = !!lens.is_active;
        openModal();
    }

    function showConfirmModal(title, description, confirmText, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-sm';
        modal.innerHTML = `
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full mx-4 border border-[#cfdbe7] dark:border-slate-800 animate-in fade-in duration-300">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="size-12 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">warning</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-lg font-bold text-[#0d141b] dark:text-white">${title}</h3>
                            <p class="text-sm text-[#4c739a]">Hành động này không thể hoàn tác</p>
                        </div>
                    </div>
                    <p class="text-sm text-[#0d141b] dark:text-slate-300 mb-6">${description}</p>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" class="cancel-btn px-4 py-2 rounded-xl border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-bold text-[#0d141b] dark:text-white hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors">
                            Hủy
                        </button>
                        <button type="button" class="confirm-btn px-4 py-2 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20">
                            ${confirmText}
                        </button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        const close = () => modal.remove();
        modal.querySelector('.cancel-btn').addEventListener('click', close);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) close();
        });
        modal.querySelector('.confirm-btn').addEventListener('click', () => {
            close();
            onConfirm();
        });
    }

    function deleteLens(id) {
        const lens = lenses.find((item) => item.id === id);
        const name = escapeHtml(lens?.name || 'lens này');
        showConfirmModal(
            'Xác nhận xóa lens',
            `Bạn có chắc muốn xóa <strong>${name}</strong>? Lens đã dùng trong đơn cũ vẫn được giữ bằng dữ liệu snapshot.`,
            'Xóa lens',
            async () => {
                const res = await fetch(`/admin/api/lenses/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (res.ok && json.success) {
                    notificationManager.success(json.message, 'Thành công');
                    loadLenses(currentPage);
                } else {
                    notificationManager.error(json.message || 'Không thể xóa lens', 'Lỗi');
                }
            }
        );
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadLenses();

        document.getElementById('open-create-lens-modal').addEventListener('click', () => {
            document.getElementById('lens-modal-title').textContent = 'Thêm Lens';
            resetForm();
            openModal();
        });
        document.getElementById('close-lens-modal').addEventListener('click', closeModal);
        document.getElementById('search-input').addEventListener('input', (e) => {
            currentSearch = e.target.value.trim();
            loadLenses(1);
        });

        document.getElementById('lens-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = document.getElementById('lens-id').value;
            const payload = {
                sku: document.getElementById('lens-sku').value.trim(),
                name: document.getElementById('lens-name').value.trim(),
                lens_type: document.getElementById('lens-type').value.trim(),
                base_price: document.getElementById('lens-price').value,
                stock_quantity: document.getElementById('lens-stock').value,
                description: document.getElementById('lens-description').value.trim(),
                requires_prescription: document.getElementById('lens-requires-prescription').checked,
                is_active: document.getElementById('lens-active').checked,
            };

            const url = id ? `/admin/api/lenses/${id}` : `{{ route('admin.api.lenses.store') }}`;
            const method = id ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });
            const json = await res.json();
            if (res.ok && json.success) {
                notificationManager.success(json.message, 'Thành công');
                closeModal();
                loadLenses(id ? currentPage : 1);
            } else {
                notificationManager.error(json.message || 'Không thể lưu lens', 'Lỗi');
            }
        });
    });
</script>
@endpush
