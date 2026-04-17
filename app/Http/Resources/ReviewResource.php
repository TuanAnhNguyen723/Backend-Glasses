<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'user' => $this->when($this->relationLoaded('user'), fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->avatar_url,
            ]),
            'replies' => $this->when($this->relationLoaded('replies'), function () {
                return $this->replies
                    ->filter(fn ($reply) => (bool) $reply->is_active)
                    ->values()
                    ->map(fn ($reply) => [
                        'id' => $reply->id,
                        'message' => $reply->message,
                        'replied_by' => $reply->user?->name ?? 'Shop',
                        'created_at' => $reply->created_at?->toIso8601String(),
                        'updated_at' => $reply->updated_at?->toIso8601String(),
                    ]);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
