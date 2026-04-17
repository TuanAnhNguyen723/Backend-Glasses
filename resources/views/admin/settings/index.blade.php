@extends('layouts.admin')

@section('title', 'Quản Trị Kính Mắt - Cài Đặt Chung')

@section('header')
    <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-8 py-6 flex flex-wrap items-center justify-between gap-6 border-b border-[#cfdbe7] dark:border-slate-800">
        <div class="flex flex-col gap-1">
            <h2 class="text-3xl font-black tracking-tight dark:text-white">Cài Đặt Chung</h2>
            <p class="text-[#4c739a] text-sm font-medium">Thiết lập nội dung dùng chung cho frontend: banner, thông báo chạy, liên hệ, địa chỉ.</p>
        </div>
        <button id="save-settings-btn" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-bold hover:bg-primary/90">
            Lưu cài đặt
        </button>
    </header>
@endsection

@section('content')
    <form id="general-settings-form" class="space-y-5">
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 p-4">
            <h3 class="text-base font-bold mb-3">Nhận Diện Thương Hiệu</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold mb-1.5">Tên cửa hàng</label>
                    <input name="store_name" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 p-4">
            <h3 class="text-base font-bold mb-3">Banner & Khuyến Mãi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold mb-1.5">Banner chính (tải từ máy)</label>
                    <label for="banner-primary-image" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold cursor-pointer hover:bg-[#f8fafc] dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined text-base">upload</span>
                        Chọn ảnh banner chính
                    </label>
                    <input id="banner-primary-image" name="banner_primary_image" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" />
                    <p id="banner-primary-file-name" class="text-[11px] text-[#4c739a] mt-1">Chưa chọn file</p>
                    <p class="text-[11px] text-[#4c739a] mt-1">PNG/JPG/WEBP, tối đa 5MB</p>
                    <img id="banner-primary-preview" class="mt-2 hidden w-full h-28 object-cover rounded-lg border border-[#e7edf3] dark:border-slate-700" alt="Banner chính preview">
                    <label class="block text-xs font-bold mb-1.5 mt-3">Link khi bấm banner chính</label>
                    <input name="banner_primary_link" placeholder="https://..." class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1.5">Banner phụ (tải từ máy)</label>
                    <label for="banner-secondary-image" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold cursor-pointer hover:bg-[#f8fafc] dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined text-base">upload</span>
                        Chọn ảnh banner phụ
                    </label>
                    <input id="banner-secondary-image" name="banner_secondary_image" type="file" accept="image/png,image/jpeg,image/webp" class="hidden" />
                    <p id="banner-secondary-file-name" class="text-[11px] text-[#4c739a] mt-1">Chưa chọn file</p>
                    <p class="text-[11px] text-[#4c739a] mt-1">PNG/JPG/WEBP, tối đa 5MB</p>
                    <img id="banner-secondary-preview" class="mt-2 hidden w-full h-28 object-cover rounded-lg border border-[#e7edf3] dark:border-slate-700" alt="Banner phụ preview">
                    <label class="block text-xs font-bold mb-1.5 mt-3">Link khi bấm banner phụ</label>
                    <input name="banner_secondary_link" placeholder="https://..." class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold mb-1.5">Dòng chạy thông báo giảm giá</label>
                    <input name="promo_ticker_text" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" name="promo_ticker_enabled" class="h-4 w-4 accent-primary" />
                    Bật dòng chạy khuyến mãi
                </label>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 p-4">
            <h3 class="text-base font-bold mb-3">Thông Tin Liên Hệ</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold mb-1.5">Số điện thoại</label>
                    <input name="contact_phone" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1.5">Email</label>
                    <input name="contact_email" type="email" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold mb-1.5">Địa chỉ cửa hàng</label>
                    <textarea name="store_address" rows="2" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3"></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-[#cfdbe7] dark:border-slate-800 p-4">
            <h3 class="text-base font-bold mb-3">Mạng Xã Hội & Footer</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold mb-1.5">Facebook URL</label>
                    <input name="facebook_url" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1.5">Zalo URL</label>
                    <input name="zalo_url" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1.5">YouTube URL</label>
                    <input name="youtube_url" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
                <div>
                    <label class="block text-xs font-bold mb-1.5">Copyright</label>
                    <input name="copyright_text" class="w-full rounded-lg border-[#cfdbe7] dark:border-slate-700 bg-white dark:bg-slate-800 text-sm py-2 px-3" />
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const form = document.getElementById('general-settings-form');
        const saveBtn = document.getElementById('save-settings-btn');
        const bannerPrimaryPreview = document.getElementById('banner-primary-preview');
        const bannerSecondaryPreview = document.getElementById('banner-secondary-preview');
        const bannerPrimaryFileName = document.getElementById('banner-primary-file-name');
        const bannerSecondaryFileName = document.getElementById('banner-secondary-file-name');

        function setFormLoading(isLoading) {
            saveBtn.disabled = isLoading;
            saveBtn.textContent = isLoading ? 'Đang lưu...' : 'Lưu cài đặt';
        }

        function setFormValues(data) {
            Object.entries(data || {}).forEach(([key, value]) => {
                const field = form.elements.namedItem(key);
                if (!field) return;
                if (field.type === 'checkbox') {
                    field.checked = !!value;
                } else {
                    field.value = value ?? '';
                }
            });

            if (data?.banner_primary_url) {
                bannerPrimaryPreview.src = data.banner_primary_url;
                bannerPrimaryPreview.classList.remove('hidden');
            } else {
                bannerPrimaryPreview.classList.add('hidden');
                bannerPrimaryPreview.src = '';
            }

            if (data?.banner_secondary_url) {
                bannerSecondaryPreview.src = data.banner_secondary_url;
                bannerSecondaryPreview.classList.remove('hidden');
            } else {
                bannerSecondaryPreview.classList.add('hidden');
                bannerSecondaryPreview.src = '';
            }
        }

        async function loadSettings() {
            try {
                const res = await fetch("{{ route('admin.api.settings.general') }}");
                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || 'Không thể tải cài đặt');
                setFormValues(payload.data);
            } catch (error) {
                notificationManager.error(error.message, 'Lỗi');
            }
        }

        async function saveSettings() {
            const payload = new FormData();
            payload.append('store_name', form.store_name.value.trim());
            payload.append('promo_ticker_enabled', form.promo_ticker_enabled.checked ? '1' : '0');
            payload.append('promo_ticker_text', form.promo_ticker_text.value.trim());
            payload.append('banner_primary_link', form.banner_primary_link.value.trim());
            payload.append('banner_secondary_link', form.banner_secondary_link.value.trim());
            payload.append('contact_phone', form.contact_phone.value.trim());
            payload.append('contact_email', form.contact_email.value.trim());
            payload.append('store_address', form.store_address.value.trim());
            payload.append('facebook_url', form.facebook_url.value.trim());
            payload.append('zalo_url', form.zalo_url.value.trim());
            payload.append('youtube_url', form.youtube_url.value.trim());
            payload.append('copyright_text', form.copyright_text.value.trim());
            payload.append('_method', 'PUT');

            const primaryFile = form.banner_primary_image.files?.[0];
            const secondaryFile = form.banner_secondary_image.files?.[0];
            if (primaryFile) payload.append('banner_primary_image', primaryFile);
            if (secondaryFile) payload.append('banner_secondary_image', secondaryFile);

            try {
                setFormLoading(true);
                const res = await fetch("{{ route('admin.api.settings.general.update') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: payload,
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Không thể lưu cài đặt');
                notificationManager.success(data.message || 'Đã lưu cài đặt');
                setFormValues(data.data);
                form.banner_primary_image.value = '';
                form.banner_secondary_image.value = '';
                bannerPrimaryFileName.textContent = 'Chưa chọn file';
                bannerSecondaryFileName.textContent = 'Chưa chọn file';
            } catch (error) {
                notificationManager.error(error.message, 'Lỗi');
            } finally {
                setFormLoading(false);
            }
        }

        function bindImagePreview(inputEl, previewEl) {
            inputEl.addEventListener('change', () => {
                const file = inputEl.files?.[0];
                if (!file) return;
                previewEl.src = URL.createObjectURL(file);
                previewEl.classList.remove('hidden');
                if (inputEl.id === 'banner-primary-image') {
                    bannerPrimaryFileName.textContent = file.name;
                } else if (inputEl.id === 'banner-secondary-image') {
                    bannerSecondaryFileName.textContent = file.name;
                }
            });
        }

        saveBtn.addEventListener('click', saveSettings);
        bindImagePreview(form.banner_primary_image, bannerPrimaryPreview);
        bindImagePreview(form.banner_secondary_image, bannerSecondaryPreview);
        document.addEventListener('DOMContentLoaded', loadSettings);
    </script>
@endpush
