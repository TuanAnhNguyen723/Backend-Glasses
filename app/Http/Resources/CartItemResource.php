<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $product = $this->resource->relationLoaded('product') ? $this->product : null;
        $primaryImageUrl = null;
        if ($product && $product->relationLoaded('primaryImage') && $product->primaryImage) {
            $img = $product->primaryImage;
            if (!empty($img->image_path)) {
                try {
                    $primaryImageUrl = Storage::disk('backblaze')->temporaryUrl($img->image_path, now()->addWeek());
                } catch (\Exception $e) {
                    $primaryImageUrl = $img->image_url ?? null;
                }
            } else {
                $primaryImageUrl = $img->image_url ?? null;
            }
        }
        $unitPrice = (float) $this->unit_price;
        $quantity = (int) $this->quantity;
        $lineTotal = round($unitPrice * $quantity, 2);

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $product?->name,
            'product_slug' => $product?->slug,
            'product_image_url' => $primaryImageUrl,
            'product_color_id' => $this->product_color_id,
            'product_color_name' => $this->productColor->name ?? null,
            'lens_option_id' => $this->lens_option_id,
            'lens_option_name' => $this->lensOption->name ?? null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
