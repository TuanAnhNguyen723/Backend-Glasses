@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Khách Hàng')

@section('header')
    <header class="h-16 flex items-center justify-between border-b border-[#e7edf3] dark:border-slate-800 bg-white dark:bg-slate-900 px-8 shrink-0 sticky top-0 z-10 backdrop-blur-md bg-white/80 dark:bg-slate-900/80">
        <div class="flex items-center gap-8">
            <h2 class="text-xl font-bold tracking-tight text-[#0d141b] dark:text-white">Khách Hàng</h2>
            <div class="relative w-72">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#4c739a] text-xl">search</span>
                <input id="search-customers" class="w-full h-10 pl-10 pr-4 bg-background-light dark:bg-slate-800 border-none rounded-xl text-sm focus:ring-2 focus:ring-primary/50 placeholder:text-[#4c739a] text-[#0d141b] dark:text-slate-100" placeholder="Tìm theo tên, email, SĐT..." type="text"/>
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
    <!-- Table Container -->
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-background-light/50 dark:bg-slate-800/50 border-b border-[#e7edf3] dark:border-slate-800">
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Khách hàng</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Liên hệ</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Số đơn</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Tổng chi tiêu</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Đơn gần nhất</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Đăng ký</th>
                        <th class="px-6 py-4 text-xs font-bold text-[#4c739a] uppercase tracking-wider w-10"></th>
                    </tr>
                </thead>
                <tbody id="customers-tbody" class="divide-y divide-[#e7edf3] dark:divide-slate-800">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-[#4c739a] dark:text-slate-400">Đang tải dữ liệu...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-[#e7edf3] dark:border-slate-800">
            <p class="text-sm text-[#4c739a] dark:text-slate-400" id="pagination-info">Hiển thị 0 đến 0 trong 0 khách hàng</p>
            <div id="pagination-controls" class="flex items-center gap-1"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            let currentPage = 1;
            let searchQuery = '';
            let searchTimeout = null;
            const perPage = 10;
            const tbody = document.getElementById('customers-tbody');
            const paginationInfo = document.getElementById('pagination-info');
            const paginationControls = document.getElementById('pagination-controls');

            function escapeHtml(text) {
                if (text == null) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function cssUrl(url) {
                if (!url) return '';
                return String(url).replace(/\\/g, '\\\\').replace(/'/g, "%27");
            }

            function getCustomers(page) {
                const params = new URLSearchParams({ page: page || 1, per_page: perPage });
                if (searchQuery) params.append('search', searchQuery);

                fetch('{{ route("admin.api.customers") }}?' + params.toString(), {
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
                        const detailBase = '{{ route("admin.customers") }}/';

                        if (data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-[#4c739a] dark:text-slate-400">Không có khách hàng nào</td></tr>';
                        } else {
                            tbody.innerHTML = data.map(function(c) {
                                const avatarStyle = c.avatar
                                    ? 'background-image: url(\'' + cssUrl(c.avatar) + '\')'
                                    : 'background-color: #e7edf3';
                                return (
                                    '<tr class="hover:bg-background-light/30 dark:hover:bg-slate-800/30 transition-colors">' +
                                    '<td class="px-6 py-4">' +
                                    '<a class="flex items-center gap-3 group" href="' + detailBase + c.id + '">' +
                                    '<div class="size-10 rounded-full bg-center bg-no-repeat bg-cover shrink-0" style="' + avatarStyle + '"></div>' +
                                    '<div><p class="font-medium text-[#0d141b] dark:text-white group-hover:text-primary">' + escapeHtml(c.name) + '</p>' +
                                    '<p class="text-xs text-[#4c739a]">ID: ' + c.id + '</p></div></a></td>' +
                                    '<td class="px-6 py-4">' +
                                    '<p class="text-sm text-[#0d141b] dark:text-white">' + escapeHtml(c.email) + '</p>' +
                                    '<p class="text-xs text-[#4c739a]">' + (c.phone ? escapeHtml(c.phone) : '—') + '</p></td>' +
                                    '<td class="px-6 py-4 font-medium text-[#0d141b] dark:text-white">' + c.orders_count + '</td>' +
                                    '<td class="px-6 py-4 text-right font-bold text-[#0d141b] dark:text-white">' + escapeHtml(c.total_spent_formatted) + ' ₫</td>' +
                                    '<td class="px-6 py-4 text-sm text-[#4c739a]">' + (c.last_order_at || '—') + '</td>' +
                                    '<td class="px-6 py-4 text-sm text-[#4c739a]">' + escapeHtml(c.created_at) + '</td>' +
                                    '<td class="px-6 py-4">' +
                                    '<a href="' + detailBase + c.id + '" class="p-2 rounded-lg hover:bg-primary/10 text-[#4c739a] hover:text-primary inline-flex"><span class="material-symbols-outlined text-xl">arrow_forward</span></a>' +
                                    '</td></tr>'
                                );
                            }).join('');
                        }

                        paginationInfo.textContent = 'Hiển thị ' + (total ? (from + ' đến ' + to) : '0') + ' trong ' + total + ' khách hàng';
                        renderPagination(current, lastPage);
                        currentPage = current;
                    })
                    .catch(function(err) {
                        console.error(err);
                        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Lỗi khi tải dữ liệu</td></tr>';
                    });
            }

            function renderPagination(current, lastPage) {
                if (lastPage <= 1) {
                    paginationControls.innerHTML = '';
                    return;
                }
                var html = '';
                html += '<button type="button" data-page="' + (current - 1) + '" class="customer-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 disabled:opacity-50" ' + (current === 1 ? 'disabled' : '') + '><span class="material-symbols-outlined text-lg">chevron_left</span></button>';
                var start = Math.max(1, current - 2);
                var end = Math.min(lastPage, current + 2);
                if (start > 1) {
                    html += '<button type="button" data-page="1" class="customer-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 text-sm">1</button>';
                    if (start > 2) html += '<span class="px-2 text-[#4c739a]">...</span>';
                }
                for (var i = start; i <= end; i++) {
                    var active = i === current;
                    html += '<button type="button" data-page="' + i + '" class="customer-page-btn size-10 flex items-center justify-center rounded-lg text-sm font-bold ' + (active ? 'bg-primary text-white' : 'hover:bg-background-light dark:hover:bg-slate-800') + '">' + i + '</button>';
                }
                if (end < lastPage) {
                    if (end < lastPage - 1) html += '<span class="px-2 text-[#4c739a]">...</span>';
                    html += '<button type="button" data-page="' + lastPage + '" class="customer-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 text-sm">' + lastPage + '</button>';
                }
                html += '<button type="button" data-page="' + (current + 1) + '" class="customer-page-btn size-10 flex items-center justify-center rounded-lg hover:bg-background-light dark:hover:bg-slate-800 disabled:opacity-50" ' + (current === lastPage ? 'disabled' : '') + '><span class="material-symbols-outlined text-lg">chevron_right</span></button>';
                paginationControls.innerHTML = html;
                paginationControls.querySelectorAll('.customer-page-btn').forEach(function(btn) {
                    var p = parseInt(btn.getAttribute('data-page'), 10);
                    if (isNaN(p) || p < 1 || p > lastPage) return;
                    btn.addEventListener('click', function() { getCustomers(p); });
                });
            }

            document.getElementById('search-customers').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchQuery = document.getElementById('search-customers').value.trim();
                    getCustomers(1);
                }, 400);
            });

            getCustomers(1);
        })();
    </script>
@endpush
