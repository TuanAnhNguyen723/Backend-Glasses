<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Generate fresh signed URL from image_path if available
        // Otherwise fallback to stored image_url
        $imageUrl = $this->image_url;
        
        if ($this->image_path) {
            try {
                // Generate fresh signed URL (valid for 1 week - max allowed)
                $imageUrl = Storage::disk('backblaze')->temporaryUrl(
                    $this->image_path,
                    now()->addWeek() // 1 week is the maximum for AWS S3 signed URLs
                );
            } catch (\Exception $e) {
                // If signed URL generation fails, use stored URL as fallback
                \Log::warning('Failed to generate signed URL for product image: ' . $e->getMessage());
            }
        }
        
        return [
            'id' => $this->id,
            'image_url' => $imageUrl, // Always return signed URL for frontend
            'is_primary' => $this->is_primary,
            'sort_order' => $this->sort_order,
            'product_color_id' => $this->product_color_id,
        ];
    }
}
