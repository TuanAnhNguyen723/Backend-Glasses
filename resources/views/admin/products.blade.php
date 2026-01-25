@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Quản Lý Sản Phẩm')

@section('header')
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-8 py-6 flex flex-wrap items-center justify-between gap-6 border-b border-[#cfdbe7] dark:border-slate-800">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight dark:text-white">Quản Lý Sản Phẩm</h2>
            <p class="text-[#4c739a] text-sm font-medium">Cấu hình và theo dõi kho hàng kính mắt của bạn.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="flex items-center gap-2 h-10 px-4 rounded-xl border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-bold text-[#0d141b] dark:text-white hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors">
                <span class="material-symbols-outlined text-lg">file_download</span>
                Xuất File
            </button>
            <a href="{{ route('admin.products.create') }}" class="flex items-center gap-2 h-10 px-5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-lg">add</span>
                Thêm Sản Phẩm Mới
            </a>
        </div>
    </header>
@endsection

@section('content')
    <!-- Search Bar -->
    <div class="bg-white dark:bg-slate-900 p-3 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm">
        <div class="flex-grow w-full">
            <label class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-4 text-[#4c739a]">search</span>
                <input id="search-input" class="w-full pl-12 pr-4 py-2.5 rounded-xl border-none bg-[#f6f7f8] dark:bg-slate-800 text-[#0d141b] dark:text-white placeholder:text-[#4c739a] focus:ring-2 focus:ring-primary/50 text-sm font-medium" placeholder="Tìm kiếm theo tên sản phẩm, SKU hoặc danh mục..." type="text"/>
            </label>
        </div>
    </div>
    <!-- Filter Dropdowns -->
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Category Filter -->
        <div class="relative">
            <button id="category-filter-btn" class="flex shrink-0 items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-[#cfdbe7] dark:border-slate-800 hover:border-primary/50 transition-all shadow-sm">
                <span class="text-xs font-bold text-[#4c739a] uppercase">Danh mục:</span>
                <span id="category-filter-text" class="text-sm font-semibold">Tất cả</span>
                <span class="material-symbols-outlined text-lg">expand_more</span>
            </button>
            <div id="category-dropdown" class="hidden absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 shadow-xl max-h-60 overflow-y-auto z-50">
                <button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="" data-type="category">
                    <span class="font-semibold">Tất cả</span>
                </button>
            </div>
        </div>
        <!-- Frame Filter -->
        <div class="relative">
            <button id="frame-filter-btn" class="flex shrink-0 items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-[#cfdbe7] dark:border-slate-800 hover:border-primary/50 transition-all shadow-sm">
                <span class="text-xs font-bold text-[#4c739a] uppercase">Khung:</span>
                <span id="frame-filter-text" class="text-sm font-semibold">Tất cả</span>
                <span class="material-symbols-outlined text-lg">expand_more</span>
            </button>
            <div id="frame-dropdown" class="hidden absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 shadow-xl max-h-60 overflow-y-auto z-50">
                <button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="" data-type="frame">
                    <span class="font-semibold">Tất cả</span>
                </button>
            </div>
        </div>
        <!-- Status Filter -->
        <div class="relative">
            <button id="status-filter-btn" class="flex shrink-0 items-center gap-2 px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-[#cfdbe7] dark:border-slate-800 hover:border-primary/50 transition-all shadow-sm">
                <span class="text-xs font-bold text-[#4c739a] uppercase">Trạng thái:</span>
                <span id="status-filter-text" class="text-sm font-semibold">Hoạt động</span>
                <span class="material-symbols-outlined text-lg">expand_more</span>
            </button>
            <div id="status-dropdown" class="hidden absolute top-full left-0 mt-2 w-48 bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 shadow-xl z-50">
                <button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="active" data-type="status">
                    <span class="font-semibold">Hoạt động</span>
                </button>
                <button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="inactive" data-type="status">
                    <span class="font-semibold">Đã lưu trữ</span>
                </button>
                <button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="" data-type="status">
                    <span class="font-semibold">Tất cả</span>
                </button>
            </div>
        </div>
    </div>
    <!-- Products Table -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto @container">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#f6f7f8]/50 dark:bg-slate-800/50 border-b border-[#cfdbe7] dark:border-slate-800">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Sản Phẩm</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Danh Mục</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Khung</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Chất Liệu</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Tag</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Giá</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Tồn Kho</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#4c739a]">Trạng Thái</th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-[#4c739a]">Thao Tác</th>
                    </tr>
                </thead>
                <tbody id="products-table-body" class="divide-y divide-[#cfdbe7] dark:divide-slate-800">
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-[#4c739a]">Đang tải dữ liệu...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-6 py-4 bg-[#f6f7f8]/50 dark:bg-slate-800/50 border-t border-[#cfdbe7] dark:border-slate-800 flex items-center justify-between">
            <span id="pagination-info" class="text-xs font-medium text-[#4c739a]">Đang tải...</span>
            <div id="pagination-controls" class="flex items-center gap-1">
                <!-- Pagination sẽ được render bằng JavaScript -->
            </div>
        </div>
    </div>
    <!-- Summary Cards Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="size-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined">inventory</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[#4c739a] text-xs font-bold uppercase tracking-wider">Tổng Tồn Kho</span>
                <span id="total-inventory" class="text-xl font-black">0 <span class="text-xs font-medium text-emerald-500 ml-1">+0%</span></span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="size-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[#4c739a] text-xs font-bold uppercase tracking-wider">Sản Phẩm Sắp Hết</span>
                <span id="low-stock-items" class="text-xl font-black">0</span>
            </div>
        </div>
        <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-[#cfdbe7] dark:border-slate-800 shadow-sm flex items-center gap-4">
            <div class="size-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <span class="material-symbols-outlined">payments</span>
            </div>
            <div class="flex flex-col">
                <span class="text-[#4c739a] text-xs font-bold uppercase tracking-wider">Giá Trị Tồn Kho</span>
                <span id="stock-value" class="text-xl font-black">0 VNĐ</span>
            </div>
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

        // State
        let currentPage = 1;
        let filters = {
            search: '',
            category: '',
            frame_shape: '',
            status: 'active'
        };

        // Load Products
        async function loadProducts(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                });
                
                // Add filters to params (only if they have values)
                if (filters.search) params.append('search', filters.search);
                if (filters.category) params.append('category', filters.category);
                if (filters.frame_shape) params.append('frame_shape', filters.frame_shape);
                if (filters.status) params.append('status', filters.status);

                const response = await fetch(`{{ route('admin.api.products') }}?${params}`);
                const data = await response.json();

                renderProducts(data.data);
                renderPagination(data);
                currentPage = page;
            } catch (error) {
                console.error('Error loading products:', error);
                document.getElementById('products-table-body').innerHTML = 
                    '<tr><td colspan="8" class="px-6 py-8 text-center text-red-500">Lỗi khi tải dữ liệu</td></tr>';
            }
        }

        // Render Products
        function renderProducts(products) {
            if (products.length === 0) {
                document.getElementById('products-table-body').innerHTML = 
                    '<tr><td colspan="9" class="px-6 py-8 text-center text-[#4c739a]">Không có sản phẩm nào</td></tr>';
                return;
            }

            const tbody = document.getElementById('products-table-body');
            tbody.innerHTML = products.map(product => {
                const frameShapeLabels = {
                    'aviator': 'Aviator',
                    'wayfarer': 'Wayfarer',
                    'round': 'Tròn',
                    'square': 'Vuông',
                    'cat-eye': 'Mắt Mèo',
                    'rectangular': 'Chữ Nhật'
                };

                // Map frame shape để hiển thị
                function getFrameShapeLabel(shape) {
                    return frameShapeLabels[shape] || shape;
                }

                const frameLabel = getFrameShapeLabel(product.frame_shape);
                const stockColor = product.stock_status === 'out' ? 'bg-red-400' : 
                                  product.stock_status === 'low' ? 'bg-amber-500' : 'bg-primary';
                
                const statusColor = product.is_active 
                    ? 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10' 
                    : 'text-red-600 bg-red-50 dark:bg-red-500/10';
                
                const statusDotColor = product.is_active ? 'bg-emerald-600' : 'bg-red-600';
                const statusLabel = product.is_active ? 'Hoạt động' : 'Đã lưu trữ';

                const stockText = product.stock_quantity === 0 
                    ? '<span class="text-xs font-bold text-red-500">Hết hàng</span>'
                    : `<span class="text-xs font-bold">${formatNumber(product.stock_quantity)} còn lại</span>`;

                return `
                    <tr class="hover:bg-[#f6f7f8] dark:hover:bg-slate-800/30 transition-colors group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-lg bg-cover bg-center border border-[#cfdbe7] dark:border-slate-700 shadow-sm ${product.stock_quantity === 0 ? 'grayscale opacity-60' : ''}" style="background-image: url('${product.image_url}')"></div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-[#0d141b] dark:text-white group-hover:text-primary transition-colors">${product.name}</span>
                                    <span class="text-xs text-[#4c739a] font-medium">SKU: ${product.sku}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-[#0d141b] dark:text-white">${product.category || '-'}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-lg ${product.frame_shape === 'aviator' ? 'bg-primary/10 text-primary' : 'bg-[#e7edf3] dark:bg-slate-800 text-[#0d141b] dark:text-slate-300'} text-xs font-bold">${frameLabel}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-[#0d141b] dark:text-white">${product.material || '-'}</span>
                        </td>
                        <td class="px-6 py-4">
                            ${product.badge ? `<span class="px-3 py-1 rounded-lg bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 text-xs font-bold">${product.badge}</span>` : '-'}
                        </td>
                        <td class="px-6 py-4 text-sm font-bold">${product.price} VNĐ</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-24 h-1.5 rounded-full bg-[#cfdbe7] dark:bg-slate-700 overflow-hidden">
                                    <div class="h-full ${stockColor}" style="width: ${product.stock_percentage}%;"></div>
                                </div>
                                ${stockText}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-lg ${statusColor} text-xs font-bold flex items-center gap-1.5 w-fit">
                                <span class="size-1.5 rounded-full ${statusDotColor}"></span>
                                ${statusLabel}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="/admin/products/${product.id}/edit" class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-lg text-[#4c739a] hover:text-primary border border-transparent hover:border-[#cfdbe7] transition-all">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </a>
                                <button class="p-2 hover:bg-white dark:hover:bg-slate-700 rounded-lg text-[#4c739a] hover:text-red-500 border border-transparent hover:border-[#cfdbe7] transition-all">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // Render Pagination
        function renderPagination(data) {
            const info = `Hiển thị ${data.from || 0}-${data.to || 0} trong tổng số ${data.total || 0} sản phẩm`;
            document.getElementById('pagination-info').textContent = info;

            const controls = document.getElementById('pagination-controls');
            if (data.last_page <= 1) {
                controls.innerHTML = '';
                return;
            }

            let html = '';
            
            // Previous button
            html += `
                <button onclick="loadProducts(${data.current_page - 1})" 
                        ${data.current_page === 1 ? 'disabled' : ''}
                        class="size-8 flex items-center justify-center rounded-lg border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-[#4c739a] hover:text-primary transition-colors disabled:opacity-50" 
                        ${data.current_page === 1 ? 'disabled' : ''}>
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </button>
            `;

            // Page numbers
            for (let i = 1; i <= Math.min(data.last_page, 5); i++) {
                const isActive = i === data.current_page;
                html += `
                    <button onclick="loadProducts(${i})" 
                            class="size-8 flex items-center justify-center rounded-lg border ${isActive ? 'border-primary bg-primary text-white' : 'border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900'} text-xs font-bold ${isActive ? '' : 'hover:text-primary'} transition-colors">
                        ${i}
                    </button>
                `;
            }

            // Next button
            html += `
                <button onclick="loadProducts(${data.current_page + 1})" 
                        ${data.current_page === data.last_page ? 'disabled' : ''}
                        class="size-8 flex items-center justify-center rounded-lg border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-[#4c739a] hover:text-primary transition-colors disabled:opacity-50" 
                        ${data.current_page === data.last_page ? 'disabled' : ''}>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            `;

            controls.innerHTML = html;
        }

        // Load Stats
        async function loadStats() {
            try {
                const response = await fetch('{{ route("admin.api.products.stats") }}');
                const data = await response.json();
                
                document.getElementById('total-inventory').innerHTML = 
                    `${formatNumber(data.total_inventory)} <span class="text-sm font-medium text-emerald-500 text-xs ml-1">+${data.inventory_growth}%</span>`;
                document.getElementById('low-stock-items').textContent = formatNumber(data.low_stock_items);
                document.getElementById('stock-value').textContent = formatCurrency(data.stock_value);
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Load Filters
        async function loadFilters() {
            try {
                const response = await fetch('{{ route("admin.api.products.filters") }}');
                const data = await response.json();
                
                // Populate Category Dropdown
                const categoryDropdown = document.getElementById('category-dropdown');
                categoryDropdown.innerHTML = '<button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="" data-type="category"><span class="font-semibold">Tất cả</span></button>';
                data.categories.forEach(category => {
                    const button = document.createElement('button');
                    button.className = 'w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option';
                    button.setAttribute('data-value', category.id);
                    button.setAttribute('data-type', 'category');
                    button.innerHTML = `<span class="font-semibold">${category.name}</span>`;
                    categoryDropdown.appendChild(button);
                });

                // Populate Frame Dropdown
                const frameDropdown = document.getElementById('frame-dropdown');
                frameDropdown.innerHTML = '<button class="w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option" data-value="" data-type="frame"><span class="font-semibold">Tất cả</span></button>';
                data.frame_shapes.forEach(shape => {
                    const button = document.createElement('button');
                    button.className = 'w-full text-left px-4 py-2 text-sm hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors filter-option';
                    button.setAttribute('data-value', shape.value);
                    button.setAttribute('data-type', 'frame');
                    button.innerHTML = `<span class="font-semibold">${shape.label}</span>`;
                    frameDropdown.appendChild(button);
                });

                window.availableFilters = data;
            } catch (error) {
                console.error('Error loading filters:', error);
            }
        }

        // Toggle Dropdown
        function toggleDropdown(dropdownId, buttonId) {
            const dropdown = document.getElementById(dropdownId);
            const button = document.getElementById(buttonId);
            
            // Close all other dropdowns
            document.querySelectorAll('[id$="-dropdown"]').forEach(dd => {
                if (dd.id !== dropdownId) {
                    dd.classList.add('hidden');
                }
            });

            dropdown.classList.toggle('hidden');
            
            // Close dropdown when clicking outside
            if (!dropdown.classList.contains('hidden')) {
                setTimeout(() => {
                    document.addEventListener('click', function closeDropdown(e) {
                        if (!dropdown.contains(e.target) && !button.contains(e.target)) {
                            dropdown.classList.add('hidden');
                            document.removeEventListener('click', closeDropdown);
                        }
                    });
                }, 100);
            }
        }

        // Handle Filter Selection
        function handleFilterSelect(value, type) {
            if (type === 'category') {
                filters.category = value;
                const text = value === '' ? 'Tất cả' : 
                    window.availableFilters?.categories.find(c => c.id == value)?.name || 'Tất cả';
                document.getElementById('category-filter-text').textContent = text;
            } else if (type === 'frame') {
                filters.frame_shape = value;
                const text = value === '' ? 'Tất cả' : 
                    window.availableFilters?.frame_shapes.find(s => s.value === value)?.label || 'Tất cả';
                document.getElementById('frame-filter-text').textContent = text;
            } else if (type === 'status') {
                filters.status = value;
                const statusLabels = {
                    'active': 'Hoạt động',
                    'inactive': 'Đã lưu trữ',
                    '': 'Tất cả'
                };
                document.getElementById('status-filter-text').textContent = statusLabels[value] || 'Tất cả';
            }

            // Close dropdown
            document.getElementById(`${type}-dropdown`).classList.add('hidden');
            
            // Reload products with new filters
            loadProducts(1);
        }

        // Search debounce
        let searchTimeout;
        document.getElementById('search-input').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filters.search = e.target.value;
                loadProducts(1);
            }, 500);
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts(1);
            loadStats();
            loadFilters().then(() => {
                // Setup dropdown toggles
                document.getElementById('category-filter-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown('category-dropdown', 'category-filter-btn');
                });

                document.getElementById('frame-filter-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown('frame-dropdown', 'frame-filter-btn');
                });

                document.getElementById('status-filter-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleDropdown('status-dropdown', 'status-filter-btn');
                });

                // Setup filter option clicks
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.filter-option')) {
                        const option = e.target.closest('.filter-option');
                        const value = option.getAttribute('data-value');
                        const type = option.getAttribute('data-type');
                        handleFilterSelect(value, type);
                    }
                });
            });
        });
    </script>
@endpush
