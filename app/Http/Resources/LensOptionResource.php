<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LensOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price_adjustment' => (float) $this->price_adjustment,
            'is_default' => (bool) $this->is_default,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];
    }
}
