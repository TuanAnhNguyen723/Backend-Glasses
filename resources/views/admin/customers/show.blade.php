@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng - ' . $customer->name)

@section('header')
    <header class="h-16 flex items-center justify-between border-b border-[#e7edf3] dark:border-slate-800 bg-white dark:bg-slate-900 px-8 shrink-0 sticky top-0 z-10 backdrop-blur-md bg-white/80 dark:bg-slate-900/80">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers') }}" class="flex items-center gap-2 text-[#4c739a] hover:text-primary transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
                <span class="text-sm font-bold">Quay lại danh sách</span>
            </a>
            <h2 class="text-xl font-bold tracking-tight text-[#0d141b] dark:text-white">{{ $customer->name }}</h2>
        </div>
    </header>
@endsection

@section('content')
    <div id="customer-detail-root" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Thông tin khách hàng -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-4">Thông tin khách hàng</h3>
                <div class="flex items-start gap-6">
                    <div id="customer-avatar" class="size-20 rounded-full bg-slate-200 dark:bg-slate-700 bg-center bg-cover shrink-0"></div>
                    <div class="flex-1 grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-bold text-[#4c739a] uppercase tracking-wider">Họ tên</p>
                            <p id="customer-name" class="font-medium text-[#0d141b] dark:text-white">—</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#4c739a] uppercase tracking-wider">Email</p>
                            <p id="customer-email" class="font-medium text-[#0d141b] dark:text-white">—</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#4c739a] uppercase tracking-wider">Số điện thoại</p>
                            <p id="customer-phone" class="font-medium text-[#0d141b] dark:text-white">—</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-[#4c739a] uppercase tracking-wider">Ngày đăng ký</p>
                            <p id="customer-created" class="font-medium text-[#0d141b] dark:text-white">—</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thống kê -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-4">Thống kê mua hàng</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-[#e7edf3] dark:border-slate-800">
                        <span class="text-[#4c739a] dark:text-slate-400">Số đơn hàng</span>
                        <span id="stats-orders-count" class="font-bold text-[#0d141b] dark:text-white">0</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-[#4c739a] dark:text-slate-400">Tổng chi tiêu</span>
                        <span id="stats-total-spent" class="font-bold text-primary">0 ₫</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng gần đây -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e7edf3] dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-[#0d141b] dark:text-white">Đơn hàng gần đây</h3>
                <a href="{{ route('admin.orders') }}?customer={{ $customer->id }}" class="text-sm font-bold text-primary hover:underline">Xem tất cả</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-background-light/50 dark:bg-slate-800/50">
                            <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Mã đơn</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Trạng thái</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Thanh toán</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Tổng</th>
                            <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Ngày</th>
                            <th class="px-6 py-3 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="recent-orders-tbody" class="divide-y divide-[#e7edf3] dark:divide-slate-800">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-[#4c739a]">Đang tải...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const customerId = {{ $customer->id }};

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

            fetch('{{ route("admin.api.customers.detail", $customer->id) }}', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function(res) { return res.json(); })
                .then(function(result) {
                    const c = result.customer || {};
                    const orders = result.recent_orders || [];

                    document.getElementById('customer-name').textContent = c.name || '—';
                    document.getElementById('customer-email').textContent = c.email || '—';
                    document.getElementById('customer-phone').textContent = c.phone || '—';
                    document.getElementById('customer-created').textContent = c.created_at || '—';
                    document.getElementById('stats-orders-count').textContent = c.orders_count || 0;
                    document.getElementById('stats-total-spent').textContent = (c.total_spent_formatted || '0') + ' ₫';

                    const avatarEl = document.getElementById('customer-avatar');
                    if (c.avatar) {
                        avatarEl.style.backgroundImage = "url('" + cssUrl(c.avatar) + "')";
                    }

                    const tbody = document.getElementById('recent-orders-tbody');
                    if (orders.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-[#4c739a]">Chưa có đơn hàng</td></tr>';
                    } else {
                        tbody.innerHTML = orders.map(function(o) {
                            const statusClass = o.status === 'delivered' ? 'bg-green-100 text-green-700' :
                                o.status === 'cancelled' ? 'bg-red-100 text-red-700' :
                                o.status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700';
                            return (
                                '<tr class="hover:bg-background-light/30 dark:hover:bg-slate-800/30">' +
                                '<td class="px-6 py-4"><a href="/admin/orders/' + o.id + '" class="font-bold text-primary hover:underline">#' + escapeHtml(o.order_number) + '</a></td>' +
                                '<td class="px-6 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-bold ' + statusClass + '">' + escapeHtml(o.status_label) + '</span></td>' +
                                '<td class="px-6 py-4 text-sm">' + (o.payment_status === 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán') + '</td>' +
                                '<td class="px-6 py-4 text-right font-bold">' + escapeHtml(o.total_formatted) + ' ₫</td>' +
                                '<td class="px-6 py-4 text-sm text-[#4c739a]">' + escapeHtml(o.created_at) + '</td>' +
                                '<td class="px-6 py-4"><a href="/admin/orders/' + o.id + '" class="p-1 rounded text-[#4c739a] hover:text-primary"><span class="material-symbols-outlined">arrow_forward</span></a></td>' +
                                '</tr>'
                            );
                        }).join('');
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    document.getElementById('recent-orders-tbody').innerHTML = '<tr><td colspan="6" class="px-6 py-8 text-center text-red-500">Lỗi khi tải dữ liệu</td></tr>';
                });
        })();
    </script>
@endpush
