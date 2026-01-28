<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RelatedProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryImageUrl = null;

        if ($this->primaryImage) {
            if ($this->primaryImage->image_path) {
                try {
                    $primaryImageUrl = Storage::disk('backblaze')->temporaryUrl(
                        $this->primaryImage->image_path,
                        now()->addWeek()
                    );
                } catch (\Exception $e) {
                    $primaryImageUrl = $this->primaryImage->image_url ?? null;
                }
            } else {
                $primaryImageUrl = $this->primaryImage->image_url ?? null;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => (float) $this->base_price,
            'compare_price' => $this->compare_price !== null ? (float) $this->compare_price : null,
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'primary_image' => $primaryImageUrl,
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
        ];
    }
}

