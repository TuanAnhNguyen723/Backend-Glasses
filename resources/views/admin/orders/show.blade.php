@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->order_number)

@section('header')
    <header class="h-16 flex items-center justify-between border-b border-[#e7edf3] dark:border-slate-800 bg-white dark:bg-slate-900 px-8 shrink-0 sticky top-0 z-10 backdrop-blur-md bg-white/80 dark:bg-slate-900/80">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.orders') }}" class="flex items-center gap-2 text-[#4c739a] hover:text-primary transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
                <span class="text-sm font-bold">Quay lại danh sách</span>
            </a>
            <h2 class="text-xl font-bold tracking-tight text-[#0d141b] dark:text-white">Đơn hàng #{{ $order->order_number }}</h2>
        </div>
    </header>
@endsection

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Chờ xử lý',
            'completed' => 'Đã đặt/Thanh toán xong',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý/Đóng gói',
            'shipped' => 'Đang giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
        ];
        $paymentLabels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'refunded' => 'Refunded',
            'failed' => 'Failed',
        ];
        $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
        $paymentLabel = $paymentLabels[$order->payment_status ?? 'pending'] ?? ucfirst($order->payment_status ?? 'pending');
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-4">Thông tin khách nhận hàng</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs uppercase font-bold tracking-wider text-[#4c739a]">Người nhận</p>
                        <p class="text-sm font-medium text-[#0d141b] dark:text-slate-100 mt-1">{{ $order->shipping_name ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold tracking-wider text-[#4c739a]">Số điện thoại</p>
                        <p class="text-sm font-medium text-[#0d141b] dark:text-slate-100 mt-1">{{ $order->shipping_phone ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold tracking-wider text-[#4c739a]">Email</p>
                        <p class="text-sm font-medium text-[#0d141b] dark:text-slate-100 mt-1">{{ $order->shipping_email ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold tracking-wider text-[#4c739a]">Địa chỉ giao hàng</p>
                        <p class="text-sm font-medium text-[#0d141b] dark:text-slate-100 mt-1 break-words">{{ $order->shipping_address ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold tracking-wider text-[#4c739a]">Tỉnh / Thành</p>
                        <p class="text-sm font-medium text-[#0d141b] dark:text-slate-100 mt-1">{{ $order->shipping_city ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold tracking-wider text-[#4c739a]">Xã / Phường</p>
                        <p class="text-sm font-medium text-[#0d141b] dark:text-slate-100 mt-1">{{ $order->shipping_ward ?: '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-[#e7edf3] dark:border-slate-800">
                    <h3 class="text-lg font-bold text-[#0d141b] dark:text-white">Sản phẩm trong đơn</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-background-light/50 dark:bg-slate-800/50 border-b border-[#e7edf3] dark:border-slate-800">
                                <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider">Sản phẩm</th>
                                <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider">SL</th>
                                <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Đơn giá</th>
                                <th class="px-6 py-3 text-xs font-bold text-[#4c739a] uppercase tracking-wider text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7edf3] dark:divide-slate-800">
                            @forelse($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-[#0d141b] dark:text-white">{{ $item->product_name }}</p>
                                        @if($item->product_color_name || $item->lens_option_name)
                                            <p class="text-xs text-[#4c739a] mt-1">
                                                {{ $item->product_color_name ?: '—' }} / {{ $item->lens_option_name ?: '—' }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#0d141b] dark:text-slate-100">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#0d141b] dark:text-slate-100 text-right">{{ number_format($item->unit_price, 0) }} ₫</td>
                                    <td class="px-6 py-4 text-sm font-bold text-[#0d141b] dark:text-white text-right">{{ number_format($item->total_price, 0) }} ₫</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-[#4c739a] dark:text-slate-400">Không có sản phẩm trong đơn hàng.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm p-6">
                <h3 class="text-lg font-bold text-[#0d141b] dark:text-white mb-4">Tóm tắt đơn hàng</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Mã đơn</span>
                        <span class="text-sm font-semibold text-[#0d141b] dark:text-white">#{{ $order->order_number }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Trạng thái</span>
                        <span class="text-sm font-semibold text-[#0d141b] dark:text-white">{{ $statusLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Thanh toán</span>
                        <span class="text-sm font-semibold text-[#0d141b] dark:text-white">{{ $paymentLabel }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Ngày đặt</span>
                        <span class="text-sm font-semibold text-[#0d141b] dark:text-white">{{ $order->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
                <div class="border-t border-[#e7edf3] dark:border-slate-800 my-4"></div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Tạm tính</span>
                        <span class="text-sm font-medium text-[#0d141b] dark:text-white">{{ number_format($order->subtotal, 0) }} ₫</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Phí vận chuyển</span>
                        <span class="text-sm font-medium text-[#0d141b] dark:text-white">{{ number_format($order->shipping_amount, 0) }} ₫</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-[#4c739a]">Giảm giá</span>
                        <span class="text-sm font-medium text-[#0d141b] dark:text-white">-{{ number_format($order->discount_amount, 0) }} ₫</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-[#e7edf3] dark:border-slate-800">
                        <span class="text-sm font-bold text-[#0d141b] dark:text-white">Tổng cộng</span>
                        <span class="text-base font-bold text-[#0d141b] dark:text-white">{{ number_format($order->total_amount, 0) }} ₫</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
