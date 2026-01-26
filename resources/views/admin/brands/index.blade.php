@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Quản Lý Thương Hiệu')

@section('header')
    <header class="flex items-center justify-between px-10 py-6 sticky top-0 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md z-10">
        <div class="flex flex-col">
            <h2 class="text-[#0d141b] dark:text-white text-3xl font-bold tracking-tight">Brand Management</h2>
            <p class="text-[#4c739a] text-sm mt-1">Manage and organize your eyewear brand portfolio</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-4 text-[#4c739a] pointer-events-none">search</span>
                <input id="search-input" class="pl-12 pr-4 py-2.5 w-64 bg-white dark:bg-slate-900 border-none rounded-full shadow-sm focus:ring-2 focus:ring-primary text-sm" placeholder="Search brands..." type="text"/>
            </div>
            <button id="add-brand-btn" class="bg-primary text-white flex items-center gap-2 px-6 py-2.5 rounded-full font-bold text-sm shadow-lg shadow-primary/25 hover:shadow-primary/40 transition-all">
                <span class="material-symbols-outlined text-xl">add</span>
                <span>Add New Brand</span>
            </button>
            <button class="p-2.5 bg-white dark:bg-slate-900 rounded-full shadow-sm text-[#4c739a] hover:text-primary transition-colors">
                <span class="material-symbols-outlined">notifications</span>
            </button>
        </div>
    </header>
@endsection

@section('content')
    <!-- Tabs/Filters -->
    <div class="px-10 mb-6">
        <div class="flex border-b border-[#cfdbe7] dark:border-slate-800 gap-8">
            <button class="filter-tab flex flex-col items-center justify-center border-b-[3px] border-primary text-[#0d141b] dark:text-white pb-3 transition-all" data-filter="all">
                <p class="text-sm font-bold leading-normal" id="all-brands-count">All Brands (0)</p>
            </button>
            <button class="filter-tab flex flex-col items-center justify-center border-b-[3px] border-transparent text-[#4c739a] pb-3 hover:text-[#0d141b] dark:hover:text-white transition-all" data-filter="active">
                <p class="text-sm font-bold leading-normal">Active</p>
            </button>
            <button class="filter-tab flex flex-col items-center justify-center border-b-[3px] border-transparent text-[#4c739a] pb-3 hover:text-[#0d141b] dark:hover:text-white transition-all" data-filter="inactive">
                <p class="text-sm font-bold leading-normal">Inactive</p>
            </button>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="px-10 pb-10">
        <div id="brands-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Loading state -->
            <div class="col-span-full text-center py-8 text-[#4c739a]">Đang tải dữ liệu...</div>
        </div>
    </div>

    <!-- Footer Pagination -->
    <footer class="mt-auto px-10 py-6 border-t border-[#e7edf3] dark:border-slate-800 flex items-center justify-between">
        <p class="text-sm text-[#4c739a]" id="pagination-info">Showing 0 of 0 brands</p>
        <div id="pagination-controls" class="flex gap-2">
            <!-- Pagination sẽ được render bằng JavaScript -->
        </div>
    </footer>
@endsection

@push('scripts')
    <script>
        // State
        let currentPage = 1;
        let currentFilter = 'all';
        let searchQuery = '';

        // Load Brands
        async function loadBrands(page = 1) {
            try {
                const params = new URLSearchParams({
                    page: page,
                    per_page: 8,
                });
                
                if (searchQuery) params.append('search', searchQuery);
                if (currentFilter === 'active') params.append('status', 'active');
                if (currentFilter === 'inactive') params.append('status', 'inactive');

                const response = await fetch(`{{ route('admin.api.brands') }}?${params}`);
                const data = await response.json();

                renderBrands(data.data);
                renderPagination(data);
                updateBrandCount(data.total);
                currentPage = page;
            } catch (error) {
                console.error('Error loading brands:', error);
                document.getElementById('brands-grid').innerHTML = 
                    '<div class="col-span-full text-center py-8 text-red-500">Lỗi khi tải dữ liệu</div>';
            }
        }

        // Render Brands
        function renderBrands(brands) {
            const grid = document.getElementById('brands-grid');
            
            if (brands.length === 0) {
                grid.innerHTML = '<div class="col-span-full text-center py-8 text-[#4c739a]">Không có thương hiệu nào</div>';
                return;
            }

            grid.innerHTML = brands.map(brand => {
                const isActive = brand.is_active;
                const statusClass = isActive ? 'bg-green-500' : 'bg-slate-300';
                const statusText = isActive ? 'Active' : 'Inactive';
                const statusTextColor = isActive ? 'text-green-500' : 'text-slate-400';
                const cardOpacity = isActive ? '' : 'opacity-90';
                const logoUrl = brand.logo_url || 'https://via.placeholder.com/64';
                
                return `
                    <div class="bg-white dark:bg-slate-900 p-6 rounded-lg shadow-sm hover:shadow-xl transition-all border border-transparent hover:border-primary/20 group ${cardOpacity}">
                        <div class="flex justify-between items-start mb-6">
                            <div class="size-16 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center overflow-hidden">
                                <div class="w-full h-full bg-center bg-cover" style="background-image: url('${logoUrl}')"></div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="editBrand(${brand.id})" class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-[#4c739a] hover:text-primary transition-colors">
                                    <span class="material-symbols-outlined text-xl">edit</span>
                                </button>
                                <button onclick="confirmDeleteBrand(${brand.id}, '${brand.name.replace(/'/g, "\\'")}')" class="p-2 rounded-full hover:bg-red-50 text-[#4c739a] hover:text-red-500 transition-colors">
                                    <span class="material-symbols-outlined text-xl">delete</span>
                                </button>
                            </div>
                        </div>
                        <div class="mb-6">
                            <h3 class="text-[#0d141b] dark:text-white text-xl font-bold">${escapeHtml(brand.name)}</h3>
                            <p class="text-[#4c739a] text-sm">${brand.product_count || 0} Products${brand.description ? ' • ' + escapeHtml(brand.description) : ''}</p>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-[#f0f2f5] dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <div class="size-2 rounded-full ${statusClass}"></div>
                                <span class="text-xs font-bold ${statusTextColor} uppercase tracking-wider">${statusText}</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" ${isActive ? 'checked' : ''} 
                                       onchange="toggleBrandStatus(${brand.id}, this.checked)"
                                       class="sr-only peer"/>
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                `;
            }).join('') + `
                <button onclick="showAddBrandModal()" class="border-2 border-dashed border-[#cfdbe7] dark:border-slate-800 rounded-lg p-6 flex flex-col items-center justify-center text-[#4c739a] hover:border-primary hover:text-primary hover:bg-primary/5 transition-all">
                    <span class="material-symbols-outlined text-4xl mb-2">add_circle</span>
                    <span class="font-bold text-sm">Add Quick Brand</span>
                </button>
            `;
        }

        // Render Pagination
        function renderPagination(data) {
            const info = `Showing ${data.from || 0}-${data.to || 0} of ${data.total || 0} brands`;
            document.getElementById('pagination-info').textContent = info;

            const controls = document.getElementById('pagination-controls');
            if (data.last_page <= 1) {
                controls.innerHTML = '';
                return;
            }

            let html = '';
            
            // Previous button
            html += `
                <button onclick="loadBrands(${data.current_page - 1})" 
                        ${data.current_page === 1 ? 'disabled' : ''}
                        class="size-10 flex items-center justify-center rounded-full bg-white dark:bg-slate-900 border border-[#e7edf3] dark:border-slate-800 text-[#4c739a] hover:bg-primary hover:text-white transition-colors disabled:opacity-50" 
                        ${data.current_page === 1 ? 'disabled' : ''}>
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>
            `;

            // Page numbers
            for (let i = 1; i <= Math.min(data.last_page, 5); i++) {
                const isActive = i === data.current_page;
                html += `
                    <button onclick="loadBrands(${i})" 
                            class="size-10 flex items-center justify-center rounded-full ${isActive ? 'bg-primary text-white' : 'bg-white dark:bg-slate-900 border border-[#e7edf3] dark:border-slate-800'} text-sm font-bold ${isActive ? '' : 'hover:bg-primary hover:text-white'} transition-colors">
                        ${i}
                    </button>
                `;
            }

            // Next button
            html += `
                <button onclick="loadBrands(${data.current_page + 1})" 
                        ${data.current_page === data.last_page ? 'disabled' : ''}
                        class="size-10 flex items-center justify-center rounded-full bg-white dark:bg-slate-900 border border-[#e7edf3] dark:border-slate-800 text-[#4c739a] hover:bg-primary hover:text-white transition-colors disabled:opacity-50" 
                        ${data.current_page === data.last_page ? 'disabled' : ''}>
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            `;

            controls.innerHTML = html;
        }

        // Update Brand Count
        function updateBrandCount(total) {
            document.getElementById('all-brands-count').textContent = `All Brands (${total})`;
        }

        // Toggle Brand Status
        async function toggleBrandStatus(brandId, isActive) {
            try {
                const response = await fetch(`{{ route('admin.api.brands.toggle-status', ':id') }}`.replace(':id', brandId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ is_active: isActive })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    notificationManager.success('Trạng thái đã được cập nhật', 'Thành công');
                    loadBrands(currentPage);
                } else {
                    notificationManager.error(data.message || 'Không thể cập nhật trạng thái', 'Lỗi');
                    loadBrands(currentPage); // Reload to revert UI
                }
            } catch (error) {
                console.error('Error toggling brand status:', error);
                notificationManager.error('Lỗi khi cập nhật trạng thái: ' + error.message, 'Lỗi');
                loadBrands(currentPage); // Reload to revert UI
            }
        }

        // Confirm and Delete Brand
        async function confirmDeleteBrand(brandId, brandName) {
            const confirmBox = document.createElement('div');
            confirmBox.className = 'fixed inset-0 z-[200] flex items-center justify-center bg-black/50 backdrop-blur-sm';
            confirmBox.innerHTML = `
                <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-md w-full mx-4 border border-[#cfdbe7] dark:border-slate-800 animate-in fade-in duration-300">
                    <div class="p-6">
                        <div class="flex items-center gap-4 mb-4">
                            <div class="size-12 rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center">
                                <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">warning</span>
                            </div>
                            <div class="flex flex-col">
                                <h3 class="text-lg font-bold text-[#0d141b] dark:text-white">Xác nhận xóa thương hiệu</h3>
                                <p class="text-sm text-[#4c739a]">Hành động này không thể hoàn tác</p>
                            </div>
                        </div>
                        <p class="text-sm text-[#0d141b] dark:text-slate-300 mb-6">
                            Bạn có chắc chắn muốn xóa thương hiệu <strong>"${escapeHtml(brandName)}"</strong>? 
                            Tất cả dữ liệu liên quan sẽ bị xóa vĩnh viễn.
                        </p>
                        <div class="flex items-center justify-end gap-3">
                            <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 rounded-xl border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-bold text-[#0d141b] dark:text-white hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors">
                                Hủy
                            </button>
                            <button onclick="deleteBrand(${brandId}, this)" class="px-4 py-2 rounded-xl bg-red-500 text-white text-sm font-bold hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20">
                                Xóa thương hiệu
                            </button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(confirmBox);

            confirmBox.addEventListener('click', function(e) {
                if (e.target === confirmBox) {
                    confirmBox.remove();
                }
            });
        }

        // Delete Brand
        async function deleteBrand(brandId, button) {
            const confirmBox = button.closest('.fixed');
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Đang xóa...';

            try {
                const response = await fetch(`/admin/api/brands/${brandId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    confirmBox.remove();
                    notificationManager.success('Thương hiệu đã được xóa thành công!', 'Thành công');
                    loadBrands(currentPage);
                } else {
                    notificationManager.error(data.message || 'Không thể xóa thương hiệu', 'Lỗi');
                    button.disabled = false;
                    button.textContent = originalText;
                }
            } catch (error) {
                console.error('Error deleting brand:', error);
                notificationManager.error('Lỗi khi xóa thương hiệu: ' + error.message, 'Lỗi');
                button.disabled = false;
                button.textContent = originalText;
            }
        }

        // Edit Brand (placeholder)
        function editBrand(brandId) {
            notificationManager.info('Chức năng chỉnh sửa sẽ được thêm sau', 'Thông tin');
        }

        // Show Add Brand Modal (placeholder)
        function showAddBrandModal() {
            notificationManager.info('Chức năng thêm thương hiệu sẽ được thêm sau', 'Thông tin');
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Search debounce
        let searchTimeout;
        document.getElementById('search-input').addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = e.target.value;
                loadBrands(1);
            }, 500);
        });

        // Filter tabs
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active state
                document.querySelectorAll('.filter-tab').forEach(t => {
                    t.classList.remove('border-primary', 'text-[#0d141b]', 'dark:text-white');
                    t.classList.add('border-transparent', 'text-[#4c739a]');
                });
                this.classList.remove('border-transparent', 'text-[#4c739a]');
                this.classList.add('border-primary', 'text-[#0d141b]', 'dark:text-white');

                currentFilter = this.getAttribute('data-filter');
                loadBrands(1);
            });
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadBrands(1);
        });
    </script>
@endpush
