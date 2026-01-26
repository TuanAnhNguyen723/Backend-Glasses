{{-- 
    Các loại thông báo (Notifications/Toasts/Alerts) cho SpecShop Admin
    
    Sử dụng:
    1. Success Notification - Thông báo thành công
    2. Error Notification - Thông báo lỗi
    3. Warning Notification - Thông báo cảnh báo
    4. Info Notification - Thông báo thông tin
    
    Mỗi thông báo có thể được điều chỉnh vị trí bằng cách thay đổi class "top-6", "top-28", "top-50", "top-72"
--}}

{{-- Success Toast --}}
<div class="fixed top-6 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
    <div class="flex items-center gap-4 bg-[#e6f4ea] border border-[#34a853]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
        <div class="flex items-center justify-center bg-[#34a853] text-white rounded-full size-8 shrink-0">
            <span class="material-symbols-outlined text-lg font-bold">check</span>
        </div>
        <div class="flex flex-col grow">
            <p class="text-[#0d652d] text-sm font-semibold leading-none">Success</p>
            <p class="text-[#0d652d] text-sm font-medium mt-1">Product updated successfully</p>
        </div>
        <button class="text-[#0d652d] hover:bg-[#34a853]/10 p-1 rounded-full transition-colors">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
    </div>
</div>

{{-- Error Toast --}}
<div class="fixed top-28 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
    <div class="flex items-center gap-4 bg-[#fef2f2] border border-[#ef4444]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
        <div class="flex items-center justify-center bg-[#ef4444] text-white rounded-full size-8 shrink-0">
            <span class="material-symbols-outlined text-lg font-bold">error</span>
        </div>
        <div class="flex flex-col grow">
            <p class="text-[#991b1b] text-sm font-semibold leading-none">Error</p>
            <p class="text-[#991b1b] text-sm font-medium mt-1">Failed to delete product</p>
        </div>
        <button class="text-[#991b1b] hover:bg-[#ef4444]/10 p-1 rounded-full transition-colors">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
    </div>
</div>

{{-- Warning Toast --}}
<div class="fixed top-50 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
    <div class="flex items-center gap-4 bg-[#fffbeb] border border-[#f59e0b]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
        <div class="flex items-center justify-center bg-[#f59e0b] text-white rounded-full size-8 shrink-0">
            <span class="material-symbols-outlined text-lg font-bold">warning</span>
        </div>
        <div class="flex flex-col grow">
            <p class="text-[#92400e] text-sm font-semibold leading-none">Warning</p>
            <p class="text-[#92400e] text-sm font-medium mt-1">Low stock: Only 5 units remaining</p>
        </div>
        <button class="text-[#92400e] hover:bg-[#f59e0b]/10 p-1 rounded-full transition-colors">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
    </div>
</div>

{{-- Info Toast --}}
<div class="fixed top-72 right-6 z-[100] animate-in fade-in slide-in-from-top-4 duration-300">
    <div class="flex items-center gap-4 bg-[#eff6ff] border border-[#3b82f6]/20 px-5 py-4 rounded-xl shadow-xl min-w-[320px]">
        <div class="flex items-center justify-center bg-[#3b82f6] text-white rounded-full size-8 shrink-0">
            <span class="material-symbols-outlined text-lg font-bold">info</span>
        </div>
        <div class="flex flex-col grow">
            <p class="text-[#1e40af] text-sm font-semibold leading-none">Info</p>
            <p class="text-[#1e40af] text-sm font-medium mt-1">New order received from customer</p>
        </div>
        <button class="text-[#1e40af] hover:bg-[#3b82f6]/10 p-1 rounded-full transition-colors">
            <span class="material-symbols-outlined text-xl">close</span>
        </button>
    </div>
</div>
