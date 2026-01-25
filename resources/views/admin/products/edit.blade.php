@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Chỉnh Sửa Sản Phẩm')

@push('styles')
    <style>
        .border-3 {
            border-width: 3px;
        }
        .tab-content.hidden {
            display: none !important;
        }
        .tab-content:not(.hidden) {
            display: block !important;
        }
    </style>
@endpush

@section('header')
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-8 py-6 flex flex-wrap items-center justify-between gap-6 border-b border-[#cfdbe7] dark:border-slate-800">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight dark:text-white">Chỉnh Sửa Sản Phẩm</h2>
            <p class="text-[#4c739a] text-sm font-medium">Cập nhật thông số, giá cả và hình ảnh cho sản phẩm.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products') }}" class="flex items-center gap-2 h-10 px-4 rounded-xl border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-bold text-[#0d141b] dark:text-white hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors">
                Hủy
            </a>
            <button id="save-product-btn" class="flex items-center gap-2 h-10 px-5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-lg">save</span>
                Cập Nhật Sản Phẩm
            </button>
        </div>
    </header>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Media -->
        <div class="lg:col-span-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] overflow-hidden border border-[#cfdbe7] dark:border-slate-800 sticky top-24">
                <div class="p-4 border-b border-[#cfdbe7] dark:border-slate-800">
                    <h3 class="text-base font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg">image</span>
                        Hình Ảnh Sản Phẩm
                    </h3>
                </div>
                <div class="p-4">
                    <!-- Drag & Drop Area -->
                    <div id="upload-area" class="border-2 border-dashed border-[#cfdbe7] dark:border-slate-700 rounded-xl p-6 flex flex-col items-center justify-center bg-[#f6f7f8] dark:bg-slate-800/50 hover:border-primary/50 transition-colors group cursor-pointer">
                        <span class="material-symbols-outlined text-3xl text-[#4c739a] group-hover:text-primary transition-colors mb-2">cloud_upload</span>
                        <p class="text-[#0d141b] dark:text-slate-200 font-bold text-sm">Nhấp để tải lên hoặc kéo thả</p>
                        <p class="text-[#4c739a] dark:text-slate-500 text-xs mt-1">PNG, JPG hoặc WEBP (Tối đa 5MB)</p>
                        <input type="file" id="file-input" multiple accept="image/png,image/jpeg,image/webp" class="hidden"/>
                    </div>
                    <!-- Gallery -->
                    <div id="image-gallery" class="mt-4 hidden">
                        <p class="text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-2 uppercase tracking-wider">Hình Ảnh Sản Phẩm</p>
                        <div id="gallery-grid" class="grid grid-cols-2 gap-2">
                            <!-- Images will be added here dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Right Column: Details & Specs -->
        <div class="lg:col-span-8">
            <!-- Tabs Navigation -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 mb-4">
                <div class="flex border-[#cfdbe7] dark:border-slate-800">
                    <button class="tab-btn active px-6 py-3 text-sm font-bold text-primary border-primary transition-colors" data-tab="basic">
                        <span class="material-symbols-outlined text-base align-middle mr-2">edit_note</span>
                        Thông Tin Cơ Bản
                    </button>
                    <button class="tab-btn px-6 py-3 text-sm font-bold text-[#4c739a] hover:text-primary transition-colors" data-tab="specs">
                        <span class="material-symbols-outlined text-base align-middle mr-2">settings_suggest</span>
                        Thông Số Kỹ Thuật
                    </button>
                    <button class="tab-btn px-6 py-3 text-sm font-bold text-[#4c739a] hover:text-primary transition-colors" data-tab="description">
                        <span class="material-symbols-outlined text-base align-middle mr-2">description</span>
                        Mô Tả
                    </button>
                </div>
            </div>
            <!-- Tab Contents -->
            <div class="space-y-4">
                <!-- Tab 1: Basic Info -->
                <div id="tab-basic" class="tab-content">
                    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-[#cfdbe7] dark:border-slate-800">
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Tên Sản Phẩm <span class="text-red-500">*</span>
                                    </label>
                                    <input id="product-name" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="VD: Kính Cận Ray-Ban Aviator Classic" type="text" required/>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        SKU <span class="text-red-500">*</span>
                                    </label>
                                    <input id="product-sku" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="VD: KC-001" type="text" required/>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Giá (VNĐ) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#4c739a] text-sm">₫</span>
                                        <input id="product-price" class="w-full pl-8 rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="0" type="number" min="0" step="1000" required/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Tồn Kho <span class="text-red-500">*</span>
                                    </label>
                                    <input id="product-stock" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="0" type="number" min="0" required/>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Ngưỡng Cảnh Báo
                                    </label>
                                    <input id="low-stock-threshold" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="10" type="number" min="0"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Danh Mục <span class="text-red-500">*</span>
                                    </label>
                                    <select id="product-category" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" required>
                                        <option value="">Chọn danh mục...</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Chất Liệu
                                    </label>
                                    <input id="product-material" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="VD: Kim loại, Nhựa..." type="text"/>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Tag (Badge)
                                    </label>
                                    <input id="product-badge" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" placeholder="VD: Bestseller, Mới..." type="text"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tab 2: Specifications -->
                <div id="tab-specs" class="tab-content hidden">
                    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-[#cfdbe7] dark:border-slate-800">
                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Hình Dạng Khung <span class="text-red-500">*</span>
                                    </label>
                                    <select id="frame-shape" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3" required>
                                        <option value="">Chọn hình dạng...</option>
                                        <option value="aviator">Aviator</option>
                                        <option value="wayfarer">Wayfarer</option>
                                        <option value="round">Tròn</option>
                                        <option value="square">Vuông</option>
                                        <option value="cat-eye">Mắt Mèo</option>
                                        <option value="rectangular">Chữ Nhật</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Loại Khung
                                    </label>
                                    <select id="frame-type" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3">
                                        <option value="full-rim">Full-rim</option>
                                        <option value="semi-rimless">Semi-rimless</option>
                                        <option value="rimless">Rimless</option>
                                        <option value="low-bridge">Low Bridge Fit</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Tương Thích Ống Kính
                                    </label>
                                    <select id="lens-compatibility" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3">
                                        <option value="single-vision">Single Vision</option>
                                        <option value="progressive">Progressive</option>
                                        <option value="reading">Reading Only</option>
                                        <option value="non-prescription">Non-Prescription</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-1.5">
                                        Trạng Thái
                                    </label>
                                    <select id="product-status" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3">
                                        <option value="1">Hoạt động</option>
                                        <option value="0">Đã lưu trữ</option>
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-2">
                                        Màu Sắc Khung Có Sẵn
                                    </label>
                                    <div id="frame-colors-container" class="flex flex-wrap gap-3">
                                        <!-- Default colors -->
                                        <button type="button" class="w-10 h-10 rounded-full bg-slate-900 border-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all frame-color-btn" data-color="#1e293b" data-color-name="Đen" style="background-color: #1e293b;"></button>
                                        <button type="button" class="w-10 h-10 rounded-full bg-amber-800 border-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all frame-color-btn" data-color="#92400e" data-color-name="Nâu" style="background-color: #92400e;"></button>
                                        <button type="button" class="w-10 h-10 rounded-full bg-slate-400 border-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all frame-color-btn" data-color="#94a3b8" data-color-name="Xám" style="background-color: #94a3b8;"></button>
                                        <button type="button" class="w-10 h-10 rounded-full bg-blue-900 border-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all frame-color-btn" data-color="#1e3a8a" data-color-name="Xanh Dương" style="background-color: #1e3a8a;"></button>
                                        <button type="button" class="w-10 h-10 rounded-full bg-rose-200 border-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all frame-color-btn" data-color="#fecdd3" data-color-name="Hồng" style="background-color: #fecdd3;"></button>
                                        <button type="button" id="add-color-btn" class="w-10 h-10 rounded-full flex items-center justify-center border-2 border-dashed border-[#cfdbe7] dark:border-slate-600 text-[#4c739a] hover:text-primary hover:border-primary transition-all">
                                            <span class="material-symbols-outlined text-sm">add</span>
                                        </button>
                                    </div>
                                    <input type="hidden" id="selected-colors" name="frame_colors" value='[]'/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Tab 3: Description -->
                <div id="tab-description" class="tab-content hidden">
                    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-[#cfdbe7] dark:border-slate-800">
                        <div class="p-4">
                            <div class="border border-[#cfdbe7] dark:border-slate-700 rounded-lg overflow-hidden">
                                <div class="bg-[#f6f7f8] dark:bg-slate-800 px-3 py-1.5 border-b border-[#cfdbe7] dark:border-slate-700 flex gap-3">
                                    <button type="button" class="text-[#4c739a] dark:text-slate-400 hover:text-primary" onclick="formatText('bold')">
                                        <span class="material-symbols-outlined text-base">format_bold</span>
                                    </button>
                                    <button type="button" class="text-[#4c739a] dark:text-slate-400 hover:text-primary" onclick="formatText('italic')">
                                        <span class="material-symbols-outlined text-base">format_italic</span>
                                    </button>
                                    <button type="button" class="text-[#4c739a] dark:text-slate-400 hover:text-primary" onclick="formatText('list')">
                                        <span class="material-symbols-outlined text-base">format_list_bulleted</span>
                                    </button>
                                    <button type="button" class="text-[#4c739a] dark:text-slate-400 hover:text-primary ml-2" onclick="formatText('link')">
                                        <span class="material-symbols-outlined text-base">link</span>
                                    </button>
                                </div>
                                <textarea id="product-description" class="w-full p-3 border-none focus:ring-0 bg-transparent text-[#0d141b] dark:text-slate-300 placeholder:text-[#4c739a] resize-none text-sm" placeholder="Mô tả chất liệu, phong cách và độ vừa vặn của kính mắt này..." rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let uploadedImages = []; // Existing images + new uploaded images
        let deletedImageIds = []; // IDs of images to delete
        let primaryImageIndex = 0;
        let selectedColors = [];
        const productId = {{ $product->id }};
        const productData = @json($product);
        const productImagesData = @json($product->images);
        const productColorsData = @json($product->colors);
        const categoriesData = @json($categories);
        
        // DOM elements - will be initialized on DOMContentLoaded
        let gallery, galleryGrid, uploadArea, fileInput;

        // Tab Navigation
        function initTabs() {
            const tabButtons = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('active', 'text-primary');
                        btn.classList.add('text-[#4c739a]');
                    });
                    
                    // Hide all tab contents
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.style.display = 'none';
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active', 'text-primary');
                    this.classList.remove('text-[#4c739a]');
                    
                    // Show corresponding tab content
                    const targetContent = document.getElementById(`tab-${targetTab}`);
                    if (targetContent) {
                        targetContent.classList.remove('hidden');
                        targetContent.style.display = 'block';
                        targetContent.style.visibility = 'visible';
                        targetContent.style.opacity = '1';
                    }
                });
            });
        }

        // Load product data from server-side data
        function loadProductData() {
            console.log('Loading product data...', productData);
            
            if (!productData) {
                console.error('Product data is null or undefined!');
                return;
            }
            
            // Fill form fields - check if elements exist first
            const nameEl = document.getElementById('product-name');
            const skuEl = document.getElementById('product-sku');
            const priceEl = document.getElementById('product-price');
            const stockEl = document.getElementById('product-stock');
            const thresholdEl = document.getElementById('low-stock-threshold');
            const categoryEl = document.getElementById('product-category');
            const frameShapeEl = document.getElementById('frame-shape');
            const materialEl = document.getElementById('product-material');
            const badgeEl = document.getElementById('product-badge');
            const descriptionEl = document.getElementById('product-description');
            const statusEl = document.getElementById('product-status');
            const frameTypeEl = document.getElementById('frame-type');
            const lensCompatEl = document.getElementById('lens-compatibility');
            
            if (nameEl) nameEl.value = productData.name || '';
            if (skuEl) skuEl.value = productData.sku || '';
            if (priceEl) priceEl.value = productData.base_price || '';
            if (stockEl) stockEl.value = productData.stock_quantity || '';
            if (thresholdEl) thresholdEl.value = productData.low_stock_threshold || '';
            if (frameShapeEl) frameShapeEl.value = productData.frame_shape || '';
            if (materialEl) materialEl.value = productData.material || '';
            if (badgeEl) badgeEl.value = productData.badge || '';
            if (descriptionEl) descriptionEl.value = productData.description || '';
            if (statusEl) statusEl.value = productData.is_active ? '1' : '0';
            
            // Category will be set after categories are loaded (handled in DOMContentLoaded)
            
            // Set frame_type and lens_compatibility if they exist in database
            if (frameTypeEl && productData.frame_type) {
                frameTypeEl.value = productData.frame_type;
            }
            if (lensCompatEl && productData.lens_compatibility) {
                lensCompatEl.value = productData.lens_compatibility;
            }
            
            // Load existing images
            console.log('Loading images...', productImagesData);
            if (productImagesData && Array.isArray(productImagesData) && productImagesData.length > 0) {
                uploadedImages = productImagesData.map((img) => ({
                    id: img.id,
                    url: img.image_url || img.url,
                    isPrimary: img.is_primary || false,
                    isExisting: true
                }));
                
                // Find primary image index
                const primaryIndex = uploadedImages.findIndex(img => img.isPrimary);
                primaryImageIndex = primaryIndex >= 0 ? primaryIndex : 0;
                
                console.log('Uploaded images:', uploadedImages, 'Primary index:', primaryImageIndex);
                
                // Ensure gallery elements are initialized
                if (!gallery) gallery = document.getElementById('image-gallery');
                if (!galleryGrid) galleryGrid = document.getElementById('gallery-grid');
                
                renderGallery();
            }
            
            // Load colors
            console.log('Loading colors...', productColorsData);
            if (productColorsData && Array.isArray(productColorsData) && productColorsData.length > 0) {
                selectedColors = productColorsData.map(c => c.hex_code || c);
                updateSelectedColorsInput();
                renderColors();
            }
        }

        // Render colors
        function renderColors() {
            const container = document.getElementById('frame-colors-container');
            const addBtn = document.getElementById('add-color-btn');
            
            // Clear existing color buttons (except add button)
            container.querySelectorAll('.frame-color-btn').forEach(btn => {
                if (btn.id !== 'add-color-btn') {
                    btn.remove();
                }
            });
            
            const colorNames = {
                '#1e293b': 'Đen',
                '#92400e': 'Nâu',
                '#94a3b8': 'Xám',
                '#1e3a8a': 'Xanh Dương',
                '#fecdd3': 'Hồng',
            };
            
            // Render selected colors
            selectedColors.forEach(color => {
                const colorBtn = document.createElement('button');
                colorBtn.type = 'button';
                colorBtn.className = 'w-10 h-10 rounded-full border-3 border-cyan-400 shadow-lg shadow-cyan-400/50 ring-4 ring-cyan-400/40 frame-color-btn active';
                colorBtn.style.backgroundColor = color;
                colorBtn.setAttribute('data-color', color);
                colorBtn.setAttribute('data-color-name', colorNames[color] || 'Tùy chỉnh');
                
                colorBtn.addEventListener('click', function() {
                    if (this.classList.contains('active')) {
                        selectedColors = selectedColors.filter(c => c !== color);
                        this.classList.remove('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
                        this.classList.add('border-transparent', 'border-2');
                    } else {
                        selectedColors.push(color);
                        this.classList.add('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
                        this.classList.remove('border-transparent', 'border-2');
                    }
                    updateSelectedColorsInput();
                });
                
                container.insertBefore(colorBtn, addBtn);
            });
        }

        // Load categories from server-side data
        function loadCategories() {
            const categorySelect = document.getElementById('product-category');
            if (!categorySelect) {
                console.error('Category select element not found!');
                return Promise.reject('Category select not found');
            }
            
            categorySelect.innerHTML = '<option value="">Chọn danh mục...</option>';
            
            if (categoriesData && Array.isArray(categoriesData)) {
                categoriesData.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
                
                console.log('Categories loaded:', categoriesData.length, 'categories');
            } else {
                console.warn('Categories data is not available');
            }
            
            return Promise.resolve();
        }
        
        // Set category value after categories are loaded
        function setCategoryValue(categoryId) {
            const categoryEl = document.getElementById('product-category');
            if (!categoryEl) {
                console.error('Category element not found!');
                return false;
            }
            
            // Check if the option exists
            const optionExists = Array.from(categoryEl.options).some(opt => opt.value == categoryId);
            if (optionExists) {
                categoryEl.value = categoryId;
                console.log('Category set successfully to:', categoryId);
                return true;
            } else {
                console.warn('Category option not found for ID:', categoryId);
                // Try again after a short delay in case options are still being added
                setTimeout(() => {
                    const optionExists = Array.from(categoryEl.options).some(opt => opt.value == categoryId);
                    if (optionExists) {
                        categoryEl.value = categoryId;
                        console.log('Category set successfully (retry) to:', categoryId);
                    } else {
                        console.error('Category option still not found after retry:', categoryId);
                    }
                }, 100);
                return false;
            }
        }

        // File upload handling - initialize on DOM ready
        function initFileUpload() {
            uploadArea = document.getElementById('upload-area');
            fileInput = document.getElementById('file-input');
            gallery = document.getElementById('image-gallery');
            galleryGrid = document.getElementById('gallery-grid');
            
            if (!uploadArea || !fileInput || !gallery || !galleryGrid) {
                console.error('File upload elements not found!');
                return;
            }
            
            uploadArea.addEventListener('click', () => fileInput.click());
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('border-primary');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('border-primary');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('border-primary');
                handleFiles(e.dataTransfer.files);
            });

            fileInput.addEventListener('change', (e) => {
                handleFiles(e.target.files);
            });
        }

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        uploadedImages.push({
                            file: file,
                            url: e.target.result,
                            isPrimary: false,
                            isExisting: false
                        });
                        renderGallery();
                    };
                    reader.readAsDataURL(file);
                } else {
                    alert('File không hợp lệ hoặc quá lớn (tối đa 5MB)');
                }
            });
        }

        function renderGallery() {
            console.log('Rendering gallery, images count:', uploadedImages.length);
            if (uploadedImages.length === 0) {
                if (gallery) gallery.classList.add('hidden');
                return;
            }
            
            if (gallery) gallery.classList.remove('hidden');
            if (!galleryGrid) {
                console.error('Gallery grid not found!');
                return;
            }
            
            galleryGrid.innerHTML = uploadedImages.map((img, index) => {
                const isPrimary = index === primaryImageIndex;
                // Handle both relative and absolute URLs
                const imageUrl = img.url.startsWith('http') ? img.url : (img.url.startsWith('/') ? img.url : '/' + img.url);
                return `
                    <div class="relative group aspect-square rounded-lg overflow-hidden border border-[#cfdbe7] dark:border-slate-700 shadow-sm">
                        <div class="w-full h-full bg-cover bg-center" style="background-image: url('${imageUrl}')"></div>
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button onclick="setPrimary(${index})" class="bg-white p-2 rounded-full text-[#0d141b] hover:text-primary" title="Đặt làm ảnh chính">
                                <span class="material-symbols-outlined text-sm">${isPrimary ? 'star' : 'star_border'}</span>
                            </button>
                            <button onclick="removeImage(${index})" class="bg-white p-2 rounded-full text-[#0d141b] hover:text-red-500">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        </div>
                        ${isPrimary ? '<div class="absolute top-2 left-2 bg-primary text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">Chính</div>' : ''}
                        ${img.isExisting ? '<div class="absolute top-2 right-2 bg-slate-700 text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">Cũ</div>' : ''}
                    </div>
                `;
            }).join('');
            console.log('Gallery rendered');
        }

        function setPrimary(index) {
            primaryImageIndex = index;
            renderGallery();
        }

        function removeImage(index) {
            const img = uploadedImages[index];
            if (img.isExisting && img.id) {
                deletedImageIds.push(img.id);
            }
            uploadedImages.splice(index, 1);
            if (primaryImageIndex >= uploadedImages.length) {
                primaryImageIndex = Math.max(0, uploadedImages.length - 1);
            }
            renderGallery();
        }

        // Text formatting
        function formatText(type) {
            const textarea = document.getElementById('product-description');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            
            let formatted = '';
            switch(type) {
                case 'bold':
                    formatted = `**${selectedText}**`;
                    break;
                case 'italic':
                    formatted = `*${selectedText}*`;
                    break;
                case 'list':
                    formatted = `- ${selectedText}`;
                    break;
                case 'link':
                    formatted = `[${selectedText}](url)`;
                    break;
            }
            
            textarea.value = textarea.value.substring(0, start) + formatted + textarea.value.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + formatted.length, start + formatted.length);
        }

        // Update product
        document.getElementById('save-product-btn').addEventListener('click', async function() {
            const formData = new FormData();
            
            // Validate required fields
            const name = document.getElementById('product-name').value.trim();
            const sku = document.getElementById('product-sku').value.trim();
            const price = document.getElementById('product-price').value;
            const stock = document.getElementById('product-stock').value;
            const category = document.getElementById('product-category').value;
            const frameShape = document.getElementById('frame-shape').value;
            
            if (!name || !sku || !price || !stock || !category || !frameShape) {
                alert('Vui lòng điền đầy đủ các trường bắt buộc');
                return;
            }
            
            // Add form data
            formData.append('name', name);
            formData.append('sku', sku);
            formData.append('base_price', price);
            formData.append('stock_quantity', stock);
            formData.append('low_stock_threshold', document.getElementById('low-stock-threshold').value || 10);
            formData.append('category_id', category);
            formData.append('frame_shape', frameShape);
            formData.append('frame_type', document.getElementById('frame-type').value);
            formData.append('lens_compatibility', document.getElementById('lens-compatibility').value);
            formData.append('material', document.getElementById('product-material').value);
            formData.append('badge', document.getElementById('product-badge').value);
            formData.append('description', document.getElementById('product-description').value);
            formData.append('is_active', document.getElementById('product-status').value);
            formData.append('frame_colors', document.getElementById('selected-colors').value || JSON.stringify(selectedColors));
            
            // Add deleted images
            if (deletedImageIds.length > 0) {
                formData.append('deleted_images', JSON.stringify(deletedImageIds));
            }
            
            // Add new images (only files, not existing images)
            const newImages = uploadedImages.filter(img => !img.isExisting);
            if (newImages.length > 0) {
                formData.append('primary_image_index', primaryImageIndex);
                newImages.forEach((img, index) => {
                    formData.append(`images[${index}]`, img.file);
                });
            } else {
                // If no new images, set primary from existing images
                formData.append('primary_image_index', primaryImageIndex);
            }
            
            try {
                const response = await fetch('{{ route("admin.api.products.update", $product->id) }}', {
                    method: 'PUT',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    alert('Lỗi: Server trả về dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin.');
                    return;
                }
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    alert('Sản phẩm đã được cập nhật thành công!');
                    window.location.href = '{{ route("admin.products") }}';
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join('\n');
                        alert('Lỗi xác thực:\n' + errorMessages);
                    } else {
                        alert('Lỗi: ' + (data.message || 'Không thể cập nhật sản phẩm'));
                    }
                }
            } catch (error) {
                console.error('Error updating product:', error);
                alert('Lỗi khi cập nhật sản phẩm: ' + error.message);
            }
        });

        function updateSelectedColorsInput() {
            document.getElementById('selected-colors').value = JSON.stringify(selectedColors);
        }

        // Frame Colors Handling
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            console.log('Product data:', productData);
            console.log('Product images:', productImagesData);
            console.log('Product colors:', productColorsData);
            
            // Initialize file upload handlers
            initFileUpload();
            
            // Initialize tabs
            initTabs();
            
            // Load categories first (synchronous now), then load product data and set category
            loadCategories().then(() => {
                console.log('Categories loaded, loading product data...');
                
                // Set category value immediately after categories are loaded
                if (productData && productData.category_id) {
                    setCategoryValue(productData.category_id);
                }
                
                // Load product data
                loadProductData();
            }).catch(error => {
                console.error('Error loading categories:', error);
                // Still try to load product data even if categories fail
                loadProductData();
            });
            
            // Frame color selection for default buttons
            document.querySelectorAll('.frame-color-btn').forEach(btn => {
                if (btn.id !== 'add-color-btn') {
                    btn.addEventListener('click', function() {
                        const color = this.getAttribute('data-color');
                        
                        if (this.classList.contains('active')) {
                            selectedColors = selectedColors.filter(c => c !== color);
                            this.classList.remove('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
                            this.classList.add('border-transparent', 'border-2');
                        } else {
                            selectedColors.push(color);
                            this.classList.add('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
                            this.classList.remove('border-transparent', 'border-2');
                        }
                        
                        updateSelectedColorsInput();
                    });
                }
            });
            
            // Add custom color button
            document.getElementById('add-color-btn')?.addEventListener('click', function() {
                const color = prompt('Nhập mã màu (hex):', '#000000');
                if (color && /^#[0-9A-F]{6}$/i.test(color)) {
                    addCustomColor(color);
                } else if (color) {
                    alert('Mã màu không hợp lệ. Vui lòng nhập mã hex (VD: #FF0000)');
                }
            });
        });

        function addCustomColor(color) {
            const container = document.getElementById('frame-colors-container');
            const addBtn = document.getElementById('add-color-btn');
            
            const colorBtn = document.createElement('button');
            colorBtn.type = 'button';
            colorBtn.className = 'w-10 h-10 rounded-full border-2 border-transparent hover:border-slate-300 dark:hover:border-slate-600 transition-all frame-color-btn';
            colorBtn.style.backgroundColor = color;
            colorBtn.setAttribute('data-color', color);
            colorBtn.setAttribute('data-color-name', 'Tùy chỉnh');
            
            colorBtn.addEventListener('click', function() {
                if (this.classList.contains('active')) {
                    selectedColors = selectedColors.filter(c => c !== color);
                    this.classList.remove('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
                    this.classList.add('border-transparent', 'border-2');
                } else {
                    selectedColors.push(color);
                    this.classList.add('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
                    this.classList.remove('border-transparent', 'border-2');
                }
                updateSelectedColorsInput();
            });
            
            container.insertBefore(colorBtn, addBtn);
            selectedColors.push(color);
            colorBtn.classList.add('active', 'border-cyan-400', 'border-3', 'ring-4', 'ring-cyan-400/40', 'shadow-lg', 'shadow-cyan-400/50');
            updateSelectedColorsInput();
        }
    </script>
@endpush
