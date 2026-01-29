@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Thêm Sản Phẩm Mới')

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
            <h2 class="text-3xl font-black tracking-tight dark:text-white">Thêm Sản Phẩm Mới</h2>
            <p class="text-[#4c739a] text-sm font-medium">Định nghĩa thông số, giá cả và hình ảnh cho sản phẩm mới của bạn.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products') }}" class="flex items-center gap-2 h-10 px-4 rounded-xl border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-900 text-sm font-bold text-[#0d141b] dark:text-white hover:bg-[#f6f7f8] dark:hover:bg-slate-800 transition-colors">
                Hủy
            </a>
            <button id="save-product-btn" class="flex items-center gap-2 h-10 px-5 rounded-xl bg-primary text-white text-sm font-bold hover:bg-primary/90 transition-all shadow-lg shadow-primary/20">
                <span class="material-symbols-outlined text-lg">save</span>
                Lưu Sản Phẩm
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
                        <p class="text-xs font-bold text-[#4c739a] dark:text-slate-300 mb-2 uppercase tracking-wider">Hình Ảnh Đã Tải Lên</p>
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
                                        Thương Hiệu
                                    </label>
                                    <select id="product-brand" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-[#0d141b] dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm py-2 px-3">
                                        <option value="">Chọn thương hiệu...</option>
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
                                    <!-- Màu sắc sẽ được lấy theo từng ảnh (color picker trên mỗi ảnh) -->
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
        let uploadedImages = [];
        let primaryImageIndex = 0;
        let lastPickedColor = '#000000';

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
                    
                    // Hide all tab contents - use both class and style
                    tabContents.forEach(content => {
                        content.classList.add('hidden');
                        content.style.display = 'none';
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active', 'text-primary');
                    this.classList.remove('text-[#4c739a]');
                    
                    // Show corresponding tab content - force display
                    const targetContent = document.getElementById(`tab-${targetTab}`);
                    if (targetContent) {
                        // Remove hidden class
                        targetContent.classList.remove('hidden');
                        // Force display with inline style to override any CSS
                        targetContent.style.display = 'block';
                        targetContent.style.visibility = 'visible';
                        targetContent.style.opacity = '1';
                        
                        // Ensure parent containers are visible
                        let parent = targetContent.parentElement;
                        while (parent && parent !== document.body) {
                            if (parent.classList.contains('hidden') || parent.style.display === 'none') {
                                parent.classList.remove('hidden');
                                parent.style.display = '';
                                parent.style.visibility = 'visible';
                            }
                            parent = parent.parentElement;
                        }
                        
                        console.log('Tab displayed:', targetContent.id, 'Display:', window.getComputedStyle(targetContent).display);
                    }
                });
            });
        }

        // Load categories
        async function loadCategories() {
            try {
                const response = await fetch('{{ route("admin.api.products.filters") }}');
                const data = await response.json();
                
                // Load categories
                const categorySelect = document.getElementById('product-category');
                data.categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
                
                // Load brands
                const brandSelect = document.getElementById('product-brand');
                if (data.brands) {
                    data.brands.forEach(brand => {
                        const option = document.createElement('option');
                        option.value = brand.id;
                        option.textContent = brand.name;
                        brandSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        }

        // File upload handling
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file-input');
        const gallery = document.getElementById('image-gallery');
        const galleryGrid = document.getElementById('gallery-grid');

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

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/') && file.size <= 5 * 1024 * 1024) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        uploadedImages.push({
                            file: file,
                            url: e.target.result,
                            isPrimary: uploadedImages.length === 0,
                            // Default: use last picked color
                            color_hex: lastPickedColor || '#000000',
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
            if (uploadedImages.length === 0) {
                gallery.classList.add('hidden');
                return;
            }
            
            gallery.classList.remove('hidden');
            galleryGrid.innerHTML = uploadedImages.map((img, index) => `
                <div class="relative group aspect-square rounded-lg overflow-hidden border border-[#cfdbe7] dark:border-slate-700 shadow-sm">
                    <div class="w-full h-full bg-cover bg-center" style="background-image: url('${img.url}')"></div>
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                        <button onclick="setPrimary(${index})" class="bg-white p-2 rounded-full text-[#0d141b] hover:text-primary" title="Đặt làm ảnh chính">
                            <span class="material-symbols-outlined text-sm">${index === primaryImageIndex ? 'star' : 'star_border'}</span>
                        </button>
                        <button onclick="removeImage(${index})" class="bg-white p-2 rounded-full text-[#0d141b] hover:text-red-500">
                            <span class="material-symbols-outlined text-sm">delete</span>
                        </button>
                    </div>
                    <div class="absolute bottom-2 left-2 flex items-center gap-2 bg-white/90 dark:bg-slate-900/90 backdrop-blur px-2 py-1 rounded-full border border-[#cfdbe7] dark:border-slate-700 custom-color-picker">
                        <input type="color" data-index="${index}" class="image-color-input custom-color-input h-7 w-7 rounded-full border border-[#cfdbe7] dark:border-slate-700 bg-transparent p-0 cursor-pointer" value="${img.color_hex || '#000000'}">
                        <span class="text-[10px] font-extrabold text-[#0d141b] dark:text-white font-mono">${(img.color_hex || '#----').toUpperCase()}</span>
                    </div>
                    ${index === primaryImageIndex ? '<div class="absolute top-2 left-2 bg-primary text-white text-[10px] font-bold px-2 py-0.5 rounded uppercase">Chính</div>' : ''}
                </div>
            `).join('');

            // Bind per-image color picker
            galleryGrid.querySelectorAll('.image-color-input').forEach((input) => {
                input.addEventListener('input', (e) => {
                    const idx = parseInt(e.target.dataset.index, 10);
                    if (!Number.isNaN(idx) && uploadedImages[idx]) {
                        const color = e.target.value;
                        uploadedImages[idx].color_hex = color;
                        lastPickedColor = color || lastPickedColor;
                        // Update label without full re-render
                        const label = e.target.parentElement?.querySelector('span');
                        if (label) label.textContent = (color || '#----').toUpperCase();
                    }
                });
            });
        }

        function setPrimary(index) {
            primaryImageIndex = index;
            renderGallery();
        }

        function removeImage(index) {
            uploadedImages.splice(index, 1);
            if (primaryImageIndex >= uploadedImages.length) {
                primaryImageIndex = 0;
            }
            renderGallery();
        }

        // Text formatting (simple implementation)
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

        // Save product
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
                notificationManager.error('Vui lòng điền đầy đủ các trường bắt buộc', 'Lỗi xác thực');
                return;
            }
            
            if (uploadedImages.length === 0) {
                notificationManager.error('Vui lòng tải lên ít nhất một hình ảnh', 'Lỗi xác thực');
                return;
            }

            // Require a color for each newly uploaded image (for variant mapping)
            const missingColorIdx = uploadedImages.findIndex(img => !img.color_hex);
            if (missingColorIdx >= 0) {
                notificationManager.error('Vui lòng chọn màu cho tất cả ảnh đã tải lên', 'Thiếu màu cho ảnh');
                return;
            }
            
            // Add form data
            formData.append('name', name);
            formData.append('sku', sku);
            formData.append('base_price', price);
            formData.append('stock_quantity', stock);
            formData.append('low_stock_threshold', document.getElementById('low-stock-threshold').value || 10);
            formData.append('category_id', category);
            formData.append('brand_id', document.getElementById('product-brand').value || '');
            formData.append('frame_shape', frameShape);
            formData.append('frame_type', document.getElementById('frame-type').value);
            formData.append('lens_compatibility', document.getElementById('lens-compatibility').value);
            formData.append('material', document.getElementById('product-material').value);
            formData.append('badge', document.getElementById('product-badge').value);
            formData.append('description', document.getElementById('product-description').value);
            formData.append('is_active', document.getElementById('product-status').value);

            // Add images (phải append file trước, không gửi frame_colors/selected-colors cũ đã xóa)
            formData.append('primary_image_index', primaryImageIndex);
            uploadedImages.forEach((img, index) => {
                formData.append(`images[${index}]`, img.file);
            });
            // Frame colors = danh sách màu unique từ từng ảnh
            const uniqueColors = Array.from(new Set(uploadedImages.map(i => (i.color_hex || '').trim()).filter(Boolean)));
            formData.append('frame_colors', JSON.stringify(uniqueColors));
            // Send image -> color mapping (index aligned with images[])
            formData.append('images_meta', JSON.stringify(uploadedImages.map(img => ({
                color_hex: img.color_hex || null,
            }))));
            
            try {
                const response = await fetch('{{ route("admin.api.products.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    notificationManager.error('Server trả về dữ liệu không hợp lệ. Vui lòng kiểm tra lại thông tin.', 'Lỗi');
                    return;
                }
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    notificationManager.success('Sản phẩm đã được tạo thành công!', 'Thành công');
                    setTimeout(() => {
                        window.location.href = '{{ route("admin.products") }}';
                    }, 1500);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        notificationManager.error(errorMessages, 'Lỗi xác thực');
                    } else {
                        notificationManager.error(data.message || 'Không thể tạo sản phẩm', 'Lỗi');
                    }
                }
            } catch (error) {
                console.error('Error saving product:', error);
                notificationManager.error('Lỗi khi lưu sản phẩm: ' + error.message, 'Lỗi');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tabs
            initTabs();
            
            // Load categories
            loadCategories();
        });
    </script>
@endpush
