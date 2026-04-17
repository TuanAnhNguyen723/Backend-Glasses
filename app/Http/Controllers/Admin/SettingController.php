<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    private const GENERAL_SETTING_KEYS = [
        'store_name',
        'banner_primary_url',
        'banner_secondary_url',
        'banner_primary_link',
        'banner_secondary_link',
        'promo_ticker_enabled',
        'promo_ticker_text',
        'contact_phone',
        'contact_email',
        'store_address',
        'facebook_url',
        'zalo_url',
        'youtube_url',
        'copyright_text',
    ];

    public function index()
    {
        return view('admin.settings.index');
    }

    public function getGeneralSettings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->loadGeneralSettings(),
        ]);
    }

    public function updateGeneralSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_name' => 'nullable|string|max:255',
            'banner_primary_url' => 'nullable|string|max:2048',
            'banner_secondary_url' => 'nullable|string|max:2048',
            'banner_primary_link' => 'nullable|string|max:2048',
            'banner_secondary_link' => 'nullable|string|max:2048',
            'banner_primary_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'banner_secondary_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'promo_ticker_enabled' => 'nullable|boolean',
            'promo_ticker_text' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'store_address' => 'nullable|string|max:500',
            'facebook_url' => 'nullable|string|max:2048',
            'zalo_url' => 'nullable|string|max:2048',
            'youtube_url' => 'nullable|string|max:2048',
            'copyright_text' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('banner_primary_image')) {
            $validated['banner_primary_url'] = $this->storeSettingImage($request->file('banner_primary_image'), 'banner_primary');
        }

        if ($request->hasFile('banner_secondary_image')) {
            $validated['banner_secondary_url'] = $this->storeSettingImage($request->file('banner_secondary_image'), 'banner_secondary');
        }

        foreach (self::GENERAL_SETTING_KEYS as $key) {
            // Tránh ghi đè null khi field không được gửi lên (đặc biệt là banner khi chỉ upload 1 ảnh)
            if (! array_key_exists($key, $validated)) {
                continue;
            }

            $value = $validated[$key];
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }
            Setting::setValue($key, $value);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật cài đặt chung.',
            'data' => $this->loadGeneralSettings(),
        ]);
    }

    private function loadGeneralSettings(): array
    {
        $defaults = [
            'store_name' => 'OPTIC STORE',
            'banner_primary_url' => '',
            'banner_secondary_url' => '',
            'banner_primary_link' => '',
            'banner_secondary_link' => '',
            'promo_ticker_enabled' => true,
            'promo_ticker_text' => 'FLASH SALE - GIẢM ĐẾN 50% TOÀN BỘ GỌNG KÍNH',
            'contact_phone' => '',
            'contact_email' => '',
            'store_address' => '',
            'facebook_url' => '',
            'zalo_url' => '',
            'youtube_url' => '',
            'copyright_text' => '',
        ];

        $data = $defaults;
        foreach (self::GENERAL_SETTING_KEYS as $key) {
            $value = Setting::getValue($key, $defaults[$key] ?? null);
            if ($key === 'promo_ticker_enabled') {
                $data[$key] = (string) $value === '1';
            } elseif (in_array($key, ['banner_primary_url', 'banner_secondary_url'], true)) {
                $data[$key] = $this->resolveImageUrl($value);
            } else {
                $data[$key] = $value ?? '';
            }
        }

        return $data;
    }

    private function storeSettingImage($file, string $prefix): string
    {
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        // Dùng cùng namespace prefix với ảnh sản phẩm (products/*) để khớp quyền key B2 hiện tại.
        $fileName = 'products/settings/' . $prefix . '_' . time() . '.' . $ext;
        $stored = Storage::disk('backblaze')->put($fileName, file_get_contents($file->getRealPath()));
        if (! $stored) {
            throw new \RuntimeException('Không thể tải ảnh banner lên B2. Vui lòng kiểm tra quyền key với prefix products/settings/*');
        }

        // Lưu path theo chuẩn product images (sẽ generate signed URL khi đọc).
        return $fileName;
    }

    private function resolveImageUrl(?string $value): string
    {
        if (! $value) {
            return '';
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        try {
            return Storage::disk('backblaze')->temporaryUrl($value, now()->addWeek());
        } catch (\Throwable $e) {
            return '';
        }
    }
}
