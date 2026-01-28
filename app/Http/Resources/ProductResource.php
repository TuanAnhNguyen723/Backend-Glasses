<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Generate fresh signed URL for primary image
        $primaryImageUrl = null;
        if ($this->primaryImage) {
            if ($this->primaryImage->image_path) {
                try {
                    // Generate fresh signed URL (valid for 1 week - max allowed)
                    $primaryImageUrl = Storage::disk('backblaze')->temporaryUrl(
                        $this->primaryImage->image_path,
                        now()->addWeek() // 1 week is the maximum for AWS S3 signed URLs
                    );
                } catch (\Exception $e) {
                    // Fallback to stored URL if signed URL generation fails
                    $primaryImageUrl = $this->primaryImage->image_url ?? null;
                    \Log::warning('Failed to generate signed URL for primary image: ' . $e->getMessage());
                }
            } else {
                $primaryImageUrl = $this->primaryImage->image_url ?? null;
            }
        }
        
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => number_format($this->base_price, 2),
            'compare_price' => $this->compare_price ? number_format($this->compare_price, 2) : null,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
                'slug' => $this->category->slug ?? null,
            ],
            'frame_shape' => $this->frame_shape,
            'material' => $this->material,
            'size' => $this->size,
            'bridge' => $this->bridge,
            'stock' => $this->stock_quantity,
            'rating' => [
                'average' => (float) $this->rating_average,
                'count' => $this->rating_count,
                'reviews' => $this->review_count,
            ],
            'badge' => $this->badge,
            'is_featured' => $this->is_featured,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'primary_image' => $primaryImageUrl, // Always return fresh signed URL
            'colors' => ProductColorResource::collection($this->whenLoaded('colors')),
            'lens_options' => LensOptionResource::collection($this->whenLoaded('lensOptions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
