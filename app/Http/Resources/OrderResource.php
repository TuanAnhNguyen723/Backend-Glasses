<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'shipping_amount' => (float) $this->shipping_amount,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount' => (float) $this->total_amount,
            'promo_code' => $this->promo_code,
            'shipping_name' => $this->shipping_name,
            'shipping_phone' => $this->shipping_phone,
            'shipping_email' => $this->shipping_email,
            'shipping_address' => $this->shipping_address,
            'shipping_city' => $this->shipping_city,
            'shipping_postal_code' => $this->shipping_postal_code,
            'shipping_country' => $this->shipping_country,
            'tracking_number' => $this->tracking_number,
            'estimated_delivery_date' => $this->estimated_delivery_date?->format('Y-m-d'),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'notes' => $this->notes,
            'payment_status' => $this->payment_status ?? 'pending',
            'payment_method' => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => $this->when($this->relationLoaded('statusHistory'), fn () => $this->statusHistory->map(fn ($h) => [
                'status' => $h->status,
                'message' => $h->message,
                'created_at' => $h->created_at?->toIso8601String(),
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
