@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Đơn Hàng')

@section('header')
    <header class="h-16 flex items-center justify-between border-b border-[#e7edf3] dark:border-slate-800 bg-white dark:bg-slate-900 px-8 shrink-0 sticky top-0 z-10 backdrop-blur-md bg-white/80 dark:bg-slate-900/80">
        <div class="flex items-center gap-8">
            <h2 class="text-xl font-bold tracking-tight text-[#0d141b] dark:text-white">Đơn Hàng</h2>
            <div class="relative w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#4c739a] text-xl">search</span>
                <input id="search-orders" class="w-full h-10 pl-10 pr-4 bg-background-light dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/50 placeholder:text-[#4c739a] text-[#0d141b] dark:text-slate-100" placeholder="Tìm kiếm đơn hàng..." type="text"/>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="size-10 flex items-center justify-center bg-background-light dark:bg-slate-800 text-[#0d141b] dark:text-slate-50 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-xl">notifications</span>
            </button>
        </div>
    </header>
@endsection

@section('content')
    <div id="bulk-actions-bar" class="hidden items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 dark:border-red-500/30 dark:bg-red-500/10">
        <span id="bulk-selected-count" class="text-sm font-semibold text-red-700 dark:text-red-300">Đã chọn 0 đơn hàng</span>
        <div class="flex items-center gap-2">
            <button id="clear-selection-btn" class="h-9 rounded-lg border border-[#e7edf3] bg-white px-3 text-sm font-semibold text-[#0d141b] hover:bg-background-light dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:hover:bg-slate-800">
                Bỏ chọn
            </button>
            <button id="bulk-delete-btn" class="h-9 rounded-lg bg-red-500 px-3 text-sm font-semibold text-white hover:bg-red-600 disabled:cursor-not-allowed disabled:opacity-50" disabled>
                Xóa đã chọn
            </button>
        </div>
    </div>
    <!-- Segmented Buttons / Filter -->
    <div class="bg-white dark:bg-slate-900 p-1.5 rounded-xl border border-[#e7edf3] dark:border-slate-800 inline-flex shadow-sm">
        <label class="cursor-pointer">
            <input class="hidden peer order-filter" name="order_filter" type="radio" value="all" checked/>
            <div class="px-6 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-primary peer-checked:text-white text-[#4c739a] hover:bg-background-light dark:hover:bg-slate-800">
                Tất cả đơn hàng
            </div>
        </label>
        <label class="cursor-pointer">
            <input class="hidden peer order-filter" name="order_filter" type="radio" value="pending"/>
            <div class="px-6 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-primary peer-checked:text-white text-[#4c739a] hover:bg-background-light dark:hover:bg-slate-800">
                Đang chờ
            </div>
        </label>
        <label class="cursor-pointer">
            <input class="hidden peer order-filter" name="order_filter" type="radio" value="completed"/>
            <div class="px-6 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-primary peer-checked:text-white text-[#4c739a] hover:bg-background-light dark:hover:bg-slate-800">
                Đã hoàn thành
            </div>
        </label>
        <label class="cursor-pointer">
            <input class="hidden peer order-filter" name="order_filter" type="radio" value="cancelled"/>
            <div class="px-6 py-2 rounded-lg text-sm font-bold transition-all peer-checked:bg-primary peer-checked:text-white text-[#4c739a] hover:bg-background-light dark:hover:bg-slate-800">
                Đã hủy
            </div>
        </label>
    </div>

    <!-- Table Container -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-background-light/50 dark:bg-slate-800/50 border-b border-[#e7edf3] dark:border-slate-800">
                        <th class="px-4 py-4 text-center">
                            <input id="select-all-orders" type="checkbox" class="size-4 rounded border-[#e7edf3] text-primary focus:ring-primary/40">
                        </th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Mã đơn</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Khách hàng</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Sản phẩm</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Thanh toán</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Tổng cộng</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Ngày</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody" class="divide-y divide-[#e7edf3] dark:divide-slate-800">
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-[#4c739a] dark:text-slate-400">Đang tải dữ liệu...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-[#e7edf3] dark:border-slate-800">
            <p class="text-sm text-[#4c739a] dark:text-slate-400" id="pagination-info">Hiển thị 0 đến 0 trong 0 đơn hàng</p>
            <div id="pagination-controls" class="flex items-center gap-1">
                <!-- Nút phân trang render bằng JS -->
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            let currentPage = 1;
            let currentFilter = 'all';
            let searchQuery = '';
            let searchTimeout = null;
            let customerFilter = (function() {
                var m = /customer=(\d+)/.exec(window.location.search);
                return m ? m[1] : '';
            })();
            let selectedOrderIds = new Set();
            let currentPageOrderIds = [];

            const perPage = 10;
            const ordersTbody = document.getElementById('orders-tbody');
            const paginationInfo = document.getElementById('pagination-info');
            const paginationControls = document.getElementById('pagination-controls');

            function escapeHtml(text) {
                if (text == null) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Không dùng escapeHtml cho URL (signed URL có '&' sẽ bị đổi thành '&amp;' -> hỏng link).
            // Chỉ escape tối thiểu để an toàn khi nhét vào CSS url('...').
            function cssUrl(url) {
                if (!url) return '';
                return String(url).replace(/\\/g, '\\\\').replace(/'/g, "%27");
            }

            function getOrders(page) {
                const params = new URLSearchParams({
                    page: page || 1,
                    per_page: perPage
                });
                if (currentFilter !== 'all') params.append('status', currentFilter);
                if (searchQuery) params.append('search', searchQuery);
                if (customerFilter) params.append('customer', customerFilter);

                fetch('{{ route("admin.api.orders") }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function(res) { return res.json(); })
                    .then(function(result) {
                        const data = result.data || [];
                        const statusOptions = result.status_options || [];
                        const total = result.total || 0;
                        const from = result.from || 0;
                        const to = result.to || 0;
                        const lastPage = result.last_page || 1;
                        const current = result.current_page || 1;
                        const updateStatusUrl = '{{ url("admin/api/orders") }}';
                        const deleteOrderUrl = '{{ url("admin/api/orders") }}';

                        if (data.length === 0) {
                            ordersTbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-[#4c739a] dark:text-slate-400">Không có đơn hàng nào</td></tr>';
                            currentPageOrderIds = [];
                            selectedOrderIds.clear();
                            updateBulkActionsUI();
                        } else {
                            currentPageOrderIds = data.map(function(order) { return order.id; });
                            selectedOrderIds = new Set(Array.from(selectedOrderIds).filter(function(id) {
                                return currentPageOrderIds.includes(id);
                            }));
                            ordersTbody.innerHTML = data.map(function(order) {
                                const productImageStyle = order.product_image_url
                                    ? 'background-image: url(\'' + cssUrl(order.product_image_url) + '\')'
                                    : 'background-color: #e7edf3';
                                const isChecked = selectedOrderIds.has(order.id) ? ' checked' : '';
                                var detailUrl = '{{ route("admin.orders") }}/' + order.id;
                                var selectOptions = statusOptions.map(function(opt) {
                                    var sel = opt.value === order.status ? ' selected' : '';
                                    return '<option value="' + escapeHtml(opt.value) + '"' + sel + '>' + escapeHtml(opt.label) + '</option>';
                                }).join('');
                                return (
                                    '<tr class="hover:bg-background-light/30 dark:hover:bg-slate-800/30 transition-colors" data-order-id="' + order.id + '">' +
                                    '<td class="px-4 py-4 text-center align-middle">' +
                                    '<input type="checkbox" class="order-select-checkbox size-4 rounded border-[#e7edf3] text-primary focus:ring-primary/40" data-order-id="' + order.id + '"' + isChecked + '>' +
                                    '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<a class="text-primary font-bold hover:underline" href="' + detailUrl + '">#' + escapeHtml(order.order_number) + '</a>' +
                                    '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<p class="font-medium text-[#0d141b] dark:text-white">' + escapeHtml(order.customer_name) + '</p>' +
                                    '<p class="text-xs text-[#4c739a]">' + escapeHtml(order.customer_email) + '</p>' +
                                    '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<div class="flex items-center gap-3">' +
                                    '<div class="size-10 rounded-lg bg-center bg-no-repeat bg-cover border border-[#e7edf3] dark:border-slate-800" style="' + productImageStyle + '" aria-hidden="true"></div>' +
                                    '<span class="text-sm font-medium text-[#0d141b] dark:text-white">' + escapeHtml(order.product_name) + '</span>' +
                                    '</div>' +
                                    '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<select class="order-status-select w-full max-w-[180px] px-3 py-1.5 text-sm rounded-lg border border-[#e7edf3] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent" data-order-id="' + order.id + '" data-previous-status="' + escapeHtml(order.status) + '">' + selectOptions + '</select>' +
                                    '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' + escapeHtml(order.payment_class) + '">' + escapeHtml(order.payment_display) + '</span>' +
                                    '</td>' +
                                    '<td class="px-6 py-4 text-right font-bold text-[#0d141b] dark:text-white">' + escapeHtml(order.total_formatted) + ' ₫</td>' +
                                    '<td class="px-6 py-4 text-sm text-[#4c739a]">' + escapeHtml(order.created_at) + '</td>' +
                                    '<td class="px-6 py-4 text-right">' +
                                    '<button type="button" class="delete-order-btn p-2 hover:bg-background-light dark:hover:bg-slate-800 rounded-lg text-[#4c739a] hover:text-red-500 transition-colors" data-order-id="' + order.id + '" data-order-number="' + escapeHtml(order.order_number) + '">' +
                                    '<span class="material-symbols-outlined text-lg">delete</span>' +
                                    '</button>' +
                                    '</td>' +
                                    '</tr>'
                                );
                            }).join('');

                            ordersTbody.querySelectorAll('.order-status-select').forEach(function(select) {
                                select.addEventListener('change', function() {
                                    var orderId = this.getAttribute('data-order-id');
                                    var newStatus = this.value;
                                    var previousStatus = this.getAttribute('data-previous-status');
                                    this.disabled = true;
                                    fetch(updateStatusUrl + '/' + orderId + '/status', {
                                        method: 'PUT',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify({ status: newStatus })
                                    })
                                        .then(function(r) { return r.json(); })
                                        .then(function(res) {
                                            this.setAttribute('data-previous-status', newStatus);
                                            if (typeof notificationManager !== 'undefined') {
                                                notificationManager.success('Đã cập nhật trạng thái đơn.', 'Thành công');
                                            } else {
                                                alert('Đã cập nhật trạng thái đơn.');
                                            }
                                        }.bind(this))
                                        .catch(function() {
                                            this.value = previousStatus;
                                            if (typeof notificationManager !== 'undefined') {
                                                notificationManager.error('Không thể cập nhật trạng thái.', 'Lỗi');
                                            } else {
                                                alert('Không thể cập nhật trạng thái.');
                                            }
                                        })
                                        .finally(function() {
                                            this.disabled = false;
                                        }.bind(this));
                                });
                            });

                            ordersTbody.querySelectorAll('.order-select-checkbox').forEach(function(checkbox) {
                                checkbox.addEventListener('change', function() {
                                    const orderId = Number(this.getAttribute('data-order-id'));
                                    if (this.checked) {
                                        selectedOrderIds.add(orderId);
                                    } else {
                                        selectedOrderIds.delete(orderId);
                                    }
                                    updateBulkActionsUI();
                                });
                            });

                            ordersTbody.querySelectorAll('.delete-order-btn').forEach(function(button) {
                                button.addEventListener('click', function() {
                                    const orderId = Number(this.getAttribute('data-order-id'));
                                    const orderNumber = this.getAttribute('data-order-number');
                                    confirmDeleteOrder(orderId, orderNumber, deleteOrderUrl);
                                });
                            });
                            updateBulkActionsUI();
                        }

                        paginationInfo.textContent = 'Hiển thị ' + (total ? (from + ' đến ' + to) : '0') + ' trong ' + total + ' đơn hàng';
                        renderPagination(current, lastPage);
                        currentPage = current;
                    })
                    .catch(function(err) {
                        console.error(err);
                        ordersTbody.innerHTML = '<tr><td colspan="9" class="px-6 py-12 text-center text-red-500">Lỗi khi tải dữ liệu</td></tr>';
                    });
            }

            function updateBulkActionsUI() {
                const selectedCount = selectedOrderIds.size;
                const bar = document.getElementById('bulk-actions-bar');
                const countLabel = document.getElementById('bulk-selected-count');
                const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
                const selectAll = document.getElementById('select-all-orders');

                countLabel.textContent = 'Đã chọn ' + selectedCount + ' đơn hàng';
                bar.classList.toggle('hidden', selectedCount === 0);
                bar.classList.toggle('flex', selectedCount > 0);
                bulkDeleteBtn.disabled = selectedCount === 0;

                const allSelected = currentPageOrderIds.length > 0 && currentPageOrderIds.every(function(id) { return selectedOrderIds.has(id); });
                const hasAnySelected = currentPageOrderIds.some(function(id) { return selectedOrderIds.has(id); });
                selectAll.checked = allSelected;
                selectAll.indeterminate = !allSelected && hasAnySelected;
            }

            function clearSelectedOrders() {
                selectedOrderIds.clear();
                document.querySelectorAll('.order-select-checkbox').forEach(function(checkbox) {
                    checkbox.checked = false;
                });
                updateBulkActionsUI();
            }

            function showConfirmModal(title, description, confirmText, onConfirm) {
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-sm';
                modal.innerHTML = `
                    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full mx-4 border border-[#e7edf3] dark:border-slate-800 animate-in fade-in duration-300">
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
                                <button type="button" class="cancel-btn px-4 py-2 rounded-xl border border-[#e7edf3] dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-bold text-[#0d141b] dark:text-white hover:bg-background-light dark:hover:bg-slate-800 transition-colors">
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

                const cleanup = function() {
                    modal.remove();
                };

                modal.querySelector('.cancel-btn').addEventListener('click', cleanup);
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) cleanup();
                });

                modal.querySelector('.confirm-btn').addEventListener('click', function() {
                    cleanup();
                    onConfirm();
                });
            }

            function confirmDeleteOrder(orderId, orderNumber, baseUrl) {
                showConfirmModal(
                    'Xác nhận xóa đơn hàng',
                    'Bạn có chắc muốn xóa đơn <strong>#' + escapeHtml(orderNumber) + '</strong>? Chỉ đơn đã giao hoặc đã hủy mới được xóa.',
                    'Xóa đơn hàng',
                    function() {
                        fetch(baseUrl + '/' + orderId, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.success) {
                                    if (typeof notificationManager !== 'undefined') {
                                        notificationManager.success(res.message || 'Đã xóa đơn hàng.', 'Thành công');
                                    }
                                    selectedOrderIds.delete(orderId);
                                    getOrders(currentPage);
                                } else {
                                    if (typeof notificationManager !== 'undefined') {
                                        notificationManager.error(res.message || 'Không thể xóa đơn hàng.', 'Lỗi');
                                    }
                                }
                            })
                            .catch(function() {
                                if (typeof notificationManager !== 'undefined') {
                                    notificationManager.error('Không thể xóa đơn hàng.', 'Lỗi');
                                }
                            });
                    }
                );
            }

            function deleteSelectedOrders() {
                const orderIds = Array.from(selectedOrderIds);
                if (orderIds.length === 0) return;
                showConfirmModal(
                    'Xác nhận xóa hàng loạt',
                    'Bạn có chắc muốn xóa <strong>' + orderIds.length + '</strong> đơn hàng đã chọn? Chỉ đơn đã giao hoặc đã hủy mới được xóa.',
                    'Xóa ' + orderIds.length + ' đơn hàng',
                    function() {
                        fetch('{{ route("admin.api.orders.bulk-destroy") }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order_ids: orderIds })
                        })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.success && res.deleted_count > 0) {
                                    if (typeof notificationManager !== 'undefined') {
                                        if (res.failed_count > 0) {
                                            notificationManager.error('Đã xóa ' + res.deleted_count + ' đơn, ' + res.failed_count + ' đơn không thể xóa.', 'Xóa một phần');
                                        } else {
                                            notificationManager.success('Đã xóa ' + res.deleted_count + ' đơn hàng.', 'Thành công');
                                        }
                                    }
                                    orderIds.forEach(function(id) { selectedOrderIds.delete(id); });
                                    getOrders(currentPage);
                                } else {
                                    var failedMessage = Array.isArray(res.failed_items) && res.failed_items.length > 0
                                        ? res.failed_items[0].message
                                        : (res.message || 'Không thể xóa các đơn hàng đã chọn.');
                                    if (typeof notificationManager !== 'undefined') {
                                        notificationManager.error(failedMessage, 'Lỗi');
                                    }
                                }
                            })
                            .catch(function() {
                                if (typeof notificationManager !== 'undefined') {
                                    notificationManager.error('Không thể xóa các đơn hàng đã chọn.', 'Lỗi');
                                }
                            });
                    }
                );
            }

            function renderPagination(current, lastPage) {
                if (lastPage <= 1) {
                    paginationControls.innerHTML = '';
                    return;
                }
                var html = '';
                html += '<button type="button" data-page="' + (current - 1) + '" class="order-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 disabled:opacity-50" ' + (current === 1 ? 'disabled' : '') + '><span class="material-symbols-outlined text-lg">chevron_left</span></button>';
                var start = Math.max(1, current - 2);
                var end = Math.min(lastPage, current + 2);
                if (start > 1) {
                    html += '<button type="button" data-page="1" class="order-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 text-sm">1</button>';
                    if (start > 2) html += '<span class="px-2 text-[#4c739a]">...</span>';
                }
                for (var i = start; i <= end; i++) {
                    var active = i === current;
                    html += '<button type="button" data-page="' + i + '" class="order-page-btn size-10 flex items-center justify-center rounded-lg text-sm font-bold ' + (active ? 'bg-primary text-white' : 'hover:bg-background-light dark:hover:bg-slate-800') + '">' + i + '</button>';
                }
                if (end < lastPage) {
                    if (end < lastPage - 1) html += '<span class="px-2 text-[#4c739a]">...</span>';
                    html += '<button type="button" data-page="' + lastPage + '" class="order-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 text-sm">' + lastPage + '</button>';
                }
                html += '<button type="button" data-page="' + (current + 1) + '" class="order-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 disabled:opacity-50" ' + (current === lastPage ? 'disabled' : '') + '><span class="material-symbols-outlined text-lg">chevron_right</span></button>';
                paginationControls.innerHTML = html;
                paginationControls.querySelectorAll('.order-page-btn').forEach(function(btn) {
                    var p = parseInt(btn.getAttribute('data-page'), 10);
                    if (isNaN(p) || p < 1 || p > lastPage) return;
                    btn.addEventListener('click', function() { getOrders(p); });
                });
            }

            document.querySelectorAll('.order-filter').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    currentFilter = this.value;
                    getOrders(1);
                });
            });

            document.getElementById('search-orders').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchQuery = document.getElementById('search-orders').value.trim();
                    getOrders(1);
                }, 400);
            });

            document.getElementById('select-all-orders').addEventListener('change', function() {
                if (this.checked) {
                    currentPageOrderIds.forEach(function(id) { selectedOrderIds.add(id); });
                } else {
                    currentPageOrderIds.forEach(function(id) { selectedOrderIds.delete(id); });
                }
                document.querySelectorAll('.order-select-checkbox').forEach(function(checkbox) {
                    checkbox.checked = document.getElementById('select-all-orders').checked;
                });
                updateBulkActionsUI();
            });
            document.getElementById('bulk-delete-btn').addEventListener('click', deleteSelectedOrders);
            document.getElementById('clear-selection-btn').addEventListener('click', clearSelectedOrders);

            getOrders(1);
        })();
    </script>
@endpush
