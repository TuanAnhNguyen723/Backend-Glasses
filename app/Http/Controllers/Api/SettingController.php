<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function general(): JsonResponse
    {
        $data = [
            'store_name' => Setting::getValue('store_name', 'OPTIC STORE'),
            'banner_primary_url' => $this->resolveImageUrl(Setting::getValue('banner_primary_url', '')),
            'banner_secondary_url' => $this->resolveImageUrl(Setting::getValue('banner_secondary_url', '')),
            'banner_primary_link' => Setting::getValue('banner_primary_link', ''),
            'banner_secondary_link' => Setting::getValue('banner_secondary_link', ''),
            'promo_ticker_enabled' => (string) Setting::getValue('promo_ticker_enabled', '1') === '1',
            'promo_ticker_text' => Setting::getValue('promo_ticker_text', ''),
            'contact_phone' => Setting::getValue('contact_phone', ''),
            'contact_email' => Setting::getValue('contact_email', ''),
            'store_address' => Setting::getValue('store_address', ''),
            'facebook_url' => Setting::getValue('facebook_url', ''),
            'zalo_url' => Setting::getValue('zalo_url', ''),
            'youtube_url' => Setting::getValue('youtube_url', ''),
            'copyright_text' => Setting::getValue('copyright_text', ''),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
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
