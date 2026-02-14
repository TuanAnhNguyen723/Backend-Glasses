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
            <button type="button" id="export-orders-btn" class="flex items-center gap-2 h-10 px-4 bg-primary text-white text-sm font-bold rounded-xl hover:bg-blue-600 transition-colors">
                <span class="material-symbols-outlined text-lg">download</span>
                <span>Export</span>
            </button>
            <button type="button" class="size-10 flex items-center justify-center bg-background-light dark:bg-slate-800 text-[#0d141b] dark:text-slate-50 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-xl">notifications</span>
            </button>
        </div>
    </header>
@endsection

@section('content')
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
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Mã đơn</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Khách hàng</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Sản phẩm</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Thanh toán</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Tổng cộng</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Ngày</th>
                    </tr>
                </thead>
                <tbody id="orders-tbody" class="divide-y divide-[#e7edf3] dark:divide-slate-800">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#4c739a] dark:text-slate-400">Đang tải dữ liệu...</td>
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

            function getOrders(page) {
                const params = new URLSearchParams({
                    page: page || 1,
                    per_page: perPage
                });
                if (currentFilter !== 'all') params.append('status', currentFilter);
                if (searchQuery) params.append('search', searchQuery);

                fetch('{{ route("admin.api.orders") }}?' + params.toString(), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function(res) { return res.json(); })
                    .then(function(result) {
                        const data = result.data || [];
                        const total = result.total || 0;
                        const from = result.from || 0;
                        const to = result.to || 0;
                        const lastPage = result.last_page || 1;
                        const current = result.current_page || 1;

                        if (data.length === 0) {
                            ordersTbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-[#4c739a] dark:text-slate-400">Không có đơn hàng nào</td></tr>';
                        } else {
                            ordersTbody.innerHTML = data.map(function(order) {
                                const productImageStyle = order.product_image_url
                                    ? 'background-image: url(\'' + escapeHtml(order.product_image_url) + '\')'
                                    : 'background-color: #e7edf3';
                                var detailUrl = '{{ route("admin.orders") }}/' + order.id;
                                return (
                                    '<tr class="hover:bg-background-light/30 dark:hover:bg-slate-800/30 transition-colors">' +
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
                                    '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' + escapeHtml(order.status_class) + '">' + escapeHtml(order.status_display) + '</span>' +
                                    '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ' + escapeHtml(order.payment_class) + '">' + escapeHtml(order.payment_display) + '</span>' +
                                    '</td>' +
                                    '<td class="px-6 py-4 text-right font-bold text-[#0d141b] dark:text-white">' + escapeHtml(order.total_formatted) + ' ₫</td>' +
                                    '<td class="px-6 py-4 text-sm text-[#4c739a]">' + escapeHtml(order.created_at) + '</td>' +
                                    '</tr>'
                                );
                            }).join('');
                        }

                        paginationInfo.textContent = 'Hiển thị ' + (total ? (from + ' đến ' + to) : '0') + ' trong ' + total + ' đơn hàng';
                        renderPagination(current, lastPage);
                        currentPage = current;
                    })
                    .catch(function(err) {
                        console.error(err);
                        ordersTbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Lỗi khi tải dữ liệu</td></tr>';
                    });
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

            document.getElementById('export-orders-btn').addEventListener('click', function() {
                if (typeof notificationManager !== 'undefined') {
                    notificationManager.info('Chức năng export sẽ được bổ sung sau.', 'Thông báo');
                } else {
                    alert('Chức năng export sẽ được bổ sung sau.');
                }
            });

            getOrders(1);
        })();
    </script>
@endpush
