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
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#e7edf3] dark:border-slate-800 shadow-sm p-6">
        <p class="text-[#4c739a] dark:text-slate-400">Trạng thái: <strong class="text-[#0d141b] dark:text-white">{{ $order->status }}</strong></p>
        <p class="text-[#4c739a] dark:text-slate-400 mt-2">Tổng cộng: <strong class="text-[#0d141b] dark:text-white">{{ number_format($order->total_amount, 2) }} ₫</strong></p>
        <p class="text-[#4c739a] dark:text-slate-400 mt-2">Khách hàng: {{ $order->user ? $order->user->name : $order->shipping_name }} ({{ $order->shipping_email }})</p>
        <p class="text-sm text-[#4c739a] dark:text-slate-400 mt-4">Trang chi tiết đơn hàng có thể mở rộng thêm nội dung sau.</p>
    </div>
@endsection
