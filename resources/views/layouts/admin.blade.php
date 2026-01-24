<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Quản Trị Kính Mắt')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#3994ef",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: {"DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px"},
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .active-nav-item {
            background-color: #3994ef20;
            color: #3994ef;
        }
        /* Loading Overlay */
        #loading-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(4px);
            transition: opacity 0.2s ease;
        }
        #loading-overlay.show {
            display: flex;
        }
        .loading-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(226, 232, 240, 0.6);
        }
        .dark .loading-card {
            background: #18181b;
            border-color: rgba(39, 39, 42, 0.8);
        }
        .loader {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(57, 148, 239, 0.2);
            border-top-color: #3994ef;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .dark .loader {
            border-color: rgba(57, 148, 239, 0.3);
            border-top-color: #3994ef;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-text {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }
        .dark .loading-text {
            color: #e4e4e7;
        }
        /* Fade transition */
        #loading-overlay {
            opacity: 0;
            pointer-events: none;
        }
        #loading-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#0d141b] dark:text-slate-100 antialiased overflow-x-hidden">
<!-- Loading Overlay -->
<div id="loading-overlay">
<div class="loading-card">
<span class="loader" aria-hidden="true"></span>
<span class="loading-text" id="loading-message">Đang tải...</span>
</div>
</div>
<div class="flex h-screen overflow-hidden">
<!-- Sidebar Navigation -->
<aside class="w-64 flex flex-col border-r border-[#cfdbe7] dark:border-slate-800 bg-background-light dark:bg-background-dark h-full">
<div class="p-6 flex flex-col gap-8 h-full">
<!-- Brand Identity -->
<div class="flex items-center gap-3">
<div class="bg-primary rounded-lg flex items-center justify-center p-2 text-white shadow-lg shadow-primary/20">
<span class="material-symbols-outlined" style="font-size: 24px;">visibility</span>
</div>
<div class="flex flex-col">
<h1 class="text-[#0d141b] dark:text-white text-lg font-bold leading-tight">OPTIC ADMIN</h1>
<p class="text-[#4c739a] dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Cửa Hàng Kính Mắt</p>
</div>
</div>
<!-- Navigation Links -->
<nav class="flex flex-col gap-2 flex-grow">
@php
    $currentRoute = Route::currentRouteName();
@endphp
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ $currentRoute === 'admin.dashboard' ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-[#e7edf3] dark:hover:bg-slate-800 transition-colors' }}" href="{{ route('admin.dashboard') }}">
<span class="material-symbols-outlined {{ $currentRoute === 'admin.dashboard' ? 'fill-1' : 'text-[#4c739a]' }}">grid_view</span>
<p class="text-sm {{ $currentRoute === 'admin.dashboard' ? 'font-bold' : 'font-semibold' }}">Tổng Quan</p>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ $currentRoute === 'admin.products' ? 'bg-primary/10 text-primary border border-primary/20' : 'hover:bg-[#e7edf3] dark:hover:bg-slate-800 transition-colors' }}" href="{{ route('admin.products') }}">
<span class="material-symbols-outlined {{ $currentRoute === 'admin.products' ? 'fill-1' : 'text-[#4c739a]' }}">inventory_2</span>
<p class="text-sm {{ $currentRoute === 'admin.products' ? 'font-bold' : 'font-semibold' }}">Kho Hàng</p>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#e7edf3] dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[#4c739a]">shopping_bag</span>
<p class="text-sm font-semibold">Đơn Hàng</p>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#e7edf3] dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[#4c739a]">group</span>
<p class="text-sm font-semibold">Khách Hàng</p>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#e7edf3] dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[#4c739a]">analytics</span>
<p class="text-sm font-semibold">Báo Cáo</p>
</a>
<div class="my-4 border-t border-[#cfdbe7] dark:border-slate-800"></div>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#e7edf3] dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined text-[#4c739a]">settings</span>
<p class="text-sm font-semibold">Cài Đặt</p>
</a>
</nav>
<!-- User Account / Logout -->
<div class="mt-auto">
<div class="flex items-center gap-3 mb-4 p-2 rounded-xl bg-white dark:bg-slate-900 border border-[#cfdbe7] dark:border-slate-800">
<div class="size-8 rounded-full bg-cover bg-center" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAl65OLd1WwL0n05WEpkSGBBox_Z4DxD1jfjZPCgwhj2-9ULxOp5zllnzO82-njyD7b_K9z9NOKOnDGlSHbT3cY3y7Yt6VIi7XgF0suK1xe1SaQtpFpYUDNqgVQOP5BIjU5Fm6XLs458i3KpYcmZBUI0pOKm5E2WYFPi2k1jthLKLwfBGta1vuPhyJW2ICqicuEOPq9Q80E0VNaolsWD_ZWrh7Qt_BfVUWsSsucEXTMORhEcx1ooNeNzZkWeest8I6xRUrfI57b5A')"></div>
<div class="flex flex-col truncate">
<p class="text-xs font-bold truncate">Quản Trị Viên</p>
<p class="text-[10px] text-[#4c739a] truncate">Quản Lý Cửa Hàng</p>
</div>
</div>
<button class="w-full flex items-center justify-center gap-2 rounded-xl h-10 px-4 bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-md shadow-primary/30">
<span class="material-symbols-outlined text-sm">logout</span>
<span>Đăng Xuất</span>
</button>
</div>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 flex flex-col overflow-y-auto">
<!-- Page Header -->
@hasSection('header')
    @yield('header')
@else
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-8 py-6 flex flex-wrap items-center justify-between gap-6 border-b border-[#cfdbe7] dark:border-slate-800">
    <div class="flex flex-col gap-1">
    <h2 class="text-3xl font-black tracking-tight dark:text-white">@yield('page-title', 'Trang Quản Trị')</h2>
    <p class="text-[#4c739a] text-sm font-medium">@yield('page-description', '')</p>
    </div>
    @hasSection('header-actions')
        <div class="flex items-center gap-3">
        @yield('header-actions')
        </div>
    @endif
    </header>
@endif
<!-- Page Content -->
<div class="p-8 flex flex-col gap-6">
@yield('content')
</div>
<!-- Footer -->
@hasSection('footer')
    @yield('footer')
@endif
</main>
</div>
@stack('scripts')
<script>
// Loading Helper Functions with counter to handle multiple concurrent requests
let loadingCounter = 0;

window.showLoading = function(message = 'Đang xử lý...') {
    loadingCounter++;
    const overlay = document.getElementById('loading-overlay');
    const messageEl = document.getElementById('loading-message');
    if (overlay && messageEl) {
        messageEl.textContent = message;
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
};

window.hideLoading = function() {
    loadingCounter = Math.max(0, loadingCounter - 1);
    if (loadingCounter === 0) {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
};

// Auto show/hide loading for fetch requests
const originalFetch = window.fetch;
window.fetch = function(...args) {
    // Only show loading for API calls, not for static assets
    const url = args[0];
    if (typeof url === 'string' && (url.includes('/api/') || url.includes('/admin/api/'))) {
        showLoading('Đang tải dữ liệu...');
        return originalFetch.apply(this, args)
            .then(response => {
                hideLoading();
                return response;
            })
            .catch(error => {
                hideLoading();
                throw error;
            });
    }
    return originalFetch.apply(this, args);
};
</script>
</body>
</html>
