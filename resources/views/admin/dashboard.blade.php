@extends('layouts.admin')

@section('title', 'Trang Quản Trị Kính Mắt - Tổng Quan')

@section('header')
<header class="sticky top-0 z-10 flex items-center justify-between bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 px-8 py-4">
<div class="flex items-center gap-6">
<h2 class="text-slate-900 dark:text-white text-xl font-bold tracking-tight">Tổng Quan Dashboard</h2>
<div class="relative w-72">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg leading-none">search</span>
<input class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-xl pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-primary/20 placeholder:text-slate-400 transition-all" placeholder="Tìm kiếm đơn hàng, khách hàng..." type="text"/>
</div>
</div>
<div class="flex items-center gap-3">
<button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-all">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-all">
<span class="material-symbols-outlined">chat_bubble</span>
</button>
<div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-700 mx-2"></div>
<button class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors shadow-sm">
<span class="material-symbols-outlined text-lg">add</span>
Sản Phẩm Mới
</button>
</div>
</header>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 rounded-lg">
<span class="material-symbols-outlined">payments</span>
</div>
<span id="revenue-growth" class="text-emerald-600 text-xs font-bold px-2 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-full">+0%</span>
</div>
<p class="text-slate-500 text-sm font-medium mb-1">Tổng Doanh Thu</p>
<p id="total-revenue" class="text-2xl font-bold text-slate-900 dark:text-white">0 VNĐ</p>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 rounded-lg">
<span class="material-symbols-outlined">shopping_cart</span>
</div>
<span id="orders-growth" class="text-blue-600 text-xs font-bold px-2 py-1 bg-blue-50 dark:bg-blue-900/20 rounded-full">+0%</span>
</div>
<p class="text-slate-500 text-sm font-medium mb-1">Tổng Đơn Hàng</p>
<p id="total-orders" class="text-2xl font-bold text-slate-900 dark:text-white">0</p>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-purple-100 dark:bg-purple-900/30 text-purple-600 rounded-lg">
<span class="material-symbols-outlined">person</span>
</div>
<span id="customers-growth" class="text-purple-600 text-xs font-bold px-2 py-1 bg-purple-50 dark:bg-purple-900/20 rounded-full">+0%</span>
</div>
<p class="text-slate-500 text-sm font-medium mb-1">Khách Hàng Hoạt Động</p>
<p id="active-customers" class="text-2xl font-bold text-slate-900 dark:text-white">0</p>
</div>
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-4">
<div class="p-2 bg-amber-100 dark:bg-amber-900/30 text-amber-600 rounded-lg">
<span class="material-symbols-outlined">sell</span>
</div>
<span id="products-growth" class="text-amber-600 text-xs font-bold px-2 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-full">+0%</span>
</div>
<p class="text-slate-500 text-sm font-medium mb-1">Sản Phẩm Đã Bán</p>
<p id="products-sold" class="text-2xl font-bold text-slate-900 dark:text-white">0</p>
</div>
</div>
<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
<!-- Sales Overview Chart -->
<div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex items-center justify-between mb-6">
<div>
<h3 class="text-slate-900 dark:text-white font-bold">Tổng Quan Doanh Số</h3>
<p class="text-slate-500 text-sm font-normal">Hiệu suất doanh thu theo tháng</p>
</div>
<select id="sales-period" class="bg-slate-50 dark:bg-slate-800 border-none rounded-lg text-xs font-semibold py-1.5 focus:ring-primary/20 cursor-pointer">
<option>6 Tháng Gần Đây</option>
<option>Năm Ngoái</option>
</select>
</div>
<div class="h-64 flex flex-col">
<div id="sales-chart" class="flex-1 relative">
<!-- Chart will be rendered here -->
</div>
</div>
</div>
<!-- Product Categories Chart -->
<div class="bg-white dark:bg-slate-900 p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<h3 class="text-slate-900 dark:text-white font-bold mb-1">Danh Mục Sản Phẩm</h3>
<p class="text-slate-500 text-sm font-normal mb-8">Bán hàng theo loại</p>
<div id="category-chart" class="relative flex items-center justify-center h-48 mb-8">
<!-- Chart will be rendered here -->
</div>
<div id="category-legend" class="space-y-3">
<!-- Legend will be rendered here -->
</div>
</div>
</div>
<!-- Recent Orders Table -->
<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
<div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
<h3 class="text-slate-900 dark:text-white font-bold">Đơn Hàng Gần Đây</h3>
<button class="text-primary text-sm font-bold hover:underline">Xem Tất Cả</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/50">
<th class="px-6 py-3 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Mã Đơn Hàng</th>
<th class="px-6 py-3 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Khách Hàng</th>
<th class="px-6 py-3 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Trạng Thái</th>
<th class="px-6 py-3 text-slate-500 text-[11px] font-bold uppercase tracking-wider">Số Tiền</th>
<th class="px-6 py-3 text-slate-500 text-[11px] font-bold uppercase tracking-wider text-right">Ngày</th>
</tr>
</thead>
<tbody id="orders-table-body" class="divide-y divide-slate-100 dark:divide-slate-800">
<!-- Orders will be loaded here -->
<tr>
<td colspan="5" class="px-6 py-8 text-center text-slate-500">Đang tải dữ liệu...</td>
</tr>
</tbody>
</table>
</div>
</div>
@endsection

@push('scripts')
<script>
// Format số tiền VNĐ
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Format số với dấu phẩy
function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

// Lấy màu cho status badge
function getStatusColor(status) {
    const colors = {
        'pending': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'processing': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'shipped': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'delivered': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
        'cancelled': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
        'refunded': 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
}

// Load Stats
async function loadStats() {
    try {
        const response = await fetch('{{ route("admin.api.stats") }}');
        const data = await response.json();
        
        document.getElementById('total-revenue').textContent = formatCurrency(data.total_revenue);
        document.getElementById('total-orders').textContent = formatNumber(data.total_orders);
        document.getElementById('active-customers').textContent = formatNumber(data.active_customers);
        document.getElementById('products-sold').textContent = formatNumber(data.products_sold);
        
        document.getElementById('revenue-growth').textContent = 
            (data.revenue_growth >= 0 ? '+' : '') + data.revenue_growth + '%';
        document.getElementById('orders-growth').textContent = 
            (data.orders_growth >= 0 ? '+' : '') + data.orders_growth + '%';
        document.getElementById('customers-growth').textContent = 
            (data.customers_growth >= 0 ? '+' : '') + data.customers_growth + '%';
        document.getElementById('products-growth').textContent = 
            (data.products_sold_growth >= 0 ? '+' : '') + data.products_sold_growth + '%';
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load Sales Overview Chart
async function loadSalesOverview() {
    try {
        const response = await fetch('{{ route("admin.api.sales-overview") }}');
        const data = await response.json();
        
        const maxRevenue = Math.max(...data.revenues, 1);
        const chartHeight = 200;
        const chartWidth = 800;
        
        let pathData = '';
        let areaData = '';
        const stepX = chartWidth / (data.months.length - 1);
        
        data.revenues.forEach((revenue, index) => {
            const x = index * stepX;
            const y = chartHeight - (revenue / maxRevenue) * chartHeight;
            
            if (index === 0) {
                pathData = `M0,${y}`;
                areaData = `M0,${y}`;
            } else {
                const prevX = (index - 1) * stepX;
                const prevY = chartHeight - (data.revenues[index - 1] / maxRevenue) * chartHeight;
                const cp1x = prevX + stepX / 2;
                const cp2x = x - stepX / 2;
                pathData += ` C${cp1x},${prevY} ${cp2x},${y} ${x},${y}`;
                areaData += ` C${cp1x},${prevY} ${cp2x},${y} ${x},${y}`;
            }
        });
        
        areaData += ` L${chartWidth},${chartHeight} L0,${chartHeight} Z`;
        
        const chartHtml = `
            <svg class="w-full h-full" preserveAspectRatio="none" viewBox="0 0 ${chartWidth} ${chartHeight}">
                <defs>
                    <linearGradient id="chartGradient" x1="0" x2="0" y1="0" y2="1">
                        <stop offset="0%" stop-color="#3994ef" stop-opacity="0.2"></stop>
                        <stop offset="100%" stop-color="#3994ef" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
                <path d="${areaData}" fill="url(#chartGradient)"></path>
                <path d="${pathData}" fill="none" stroke="#3994ef" stroke-linecap="round" stroke-width="3"></path>
            </svg>
            <div class="flex justify-between mt-4 px-2">
                ${data.months.map(month => `<span class="text-slate-400 text-[11px] font-bold uppercase tracking-wider">${month}</span>`).join('')}
            </div>
        `;
        
        document.getElementById('sales-chart').innerHTML = chartHtml;
    } catch (error) {
        console.error('Error loading sales overview:', error);
    }
}

// Load Category Stats
async function loadCategoryStats() {
    try {
        const response = await fetch('{{ route("admin.api.category-stats") }}');
        const data = await response.json();
        
        if (data.categories.length === 0) {
            document.getElementById('category-chart').innerHTML = 
                '<div class="text-center text-slate-500">Chưa có dữ liệu</div>';
            return;
        }
        
        const colors = ['#3994ef', '#fbbf24', '#8b5cf6', '#10b981', '#ef4444'];
        let offset = 0;
        const circumference = 2 * Math.PI * 70;
        
        const circles = data.categories.map((cat, index) => {
            const percentage = cat.percentage / 100;
            const dashArray = circumference * percentage;
            const dashOffset = circumference - dashArray + offset;
            offset -= dashArray;
            
            return `<circle cx="80" cy="80" fill="transparent" r="70" stroke="${colors[index % colors.length]}" 
                    stroke-dasharray="${circumference}" stroke-dashoffset="${dashOffset}" 
                    stroke-linecap="round" stroke-width="20"></circle>`;
        }).join('');
        
        const chartHtml = `
            <svg class="w-40 h-40 transform -rotate-90">
                <circle cx="80" cy="80" fill="transparent" r="70" stroke="#e2e8f0" stroke-width="20"></circle>
                ${circles}
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-bold text-slate-900 dark:text-white">${formatNumber(data.total_products)}</span>
                <span class="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">Tổng Sản Phẩm</span>
            </div>
        `;
        
        document.getElementById('category-chart').innerHTML = chartHtml;
        
        const legendHtml = data.categories.map((cat, index) => `
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full" style="background-color: ${colors[index % colors.length]}"></div>
                    <span class="text-slate-600 dark:text-slate-400 font-medium">${cat.name}</span>
                </div>
                <span class="text-slate-900 dark:text-white font-bold">${cat.percentage}%</span>
            </div>
        `).join('');
        
        document.getElementById('category-legend').innerHTML = legendHtml;
    } catch (error) {
        console.error('Error loading category stats:', error);
    }
}

// Load Recent Orders
async function loadRecentOrders() {
    try {
        const response = await fetch('{{ route("admin.api.recent-orders") }}');
        const orders = await response.json();
        
        if (orders.length === 0) {
            document.getElementById('orders-table-body').innerHTML = 
                '<tr><td colspan="5" class="px-6 py-8 text-center text-slate-500">Chưa có đơn hàng nào</td></tr>';
            return;
        }
        
        const ordersHtml = orders.map(order => {
            const initials = order.customer_initials || order.customer.substring(0, 2).toUpperCase();
            const bgColors = ['bg-blue-100', 'bg-slate-100', 'bg-amber-100', 'bg-emerald-100', 'bg-purple-100'];
            const textColors = ['text-blue-600', 'text-slate-600', 'text-amber-600', 'text-emerald-600', 'text-purple-600'];
            const colorIndex = initials.charCodeAt(0) % bgColors.length;
            
            return `
                <tr>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">${order.id}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full ${bgColors[colorIndex]} dark:bg-slate-800 flex items-center justify-center text-[10px] font-bold ${textColors[colorIndex]}">${initials}</div>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">${order.customer}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold ${getStatusColor(order.status)}">${order.status_label}</span>
                    </td>
                    <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">${order.amount} VNĐ</td>
                    <td class="px-6 py-4 text-sm text-slate-500 text-right">${order.date}</td>
                </tr>
            `;
        }).join('');
        
        document.getElementById('orders-table-body').innerHTML = ordersHtml;
    } catch (error) {
        console.error('Error loading recent orders:', error);
        document.getElementById('orders-table-body').innerHTML = 
            '<tr><td colspan="5" class="px-6 py-8 text-center text-red-500">Lỗi khi tải dữ liệu</td></tr>';
    }
}

// Load all data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadSalesOverview();
    loadCategoryStats();
    loadRecentOrders();
    
    // Refresh data every 30 seconds
    setInterval(() => {
        loadStats();
        loadRecentOrders();
    }, 30000);
});
</script>
@endpush
