<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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
            'primary_image' => $this->primaryImage ? $this->primaryImage->image_url : null,
            'colors' => ProductColorResource::collection($this->whenLoaded('colors')),
            'lens_options' => LensOptionResource::collection($this->whenLoaded('lensOptions')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
