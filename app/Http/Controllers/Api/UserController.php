<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Dashboard: 1 request lấy user + đơn gần đây (tối ưu cho trang profile).
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $recentOrders = $user->orders()
            ->with('items')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return response()->json([
            'user' => $this->profileArray($user),
            'recent_orders' => OrderResource::collection($recentOrders),
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json($this->profileArray($request->user()));
    }

    /**
     * Chỉ trả các field cần cho profile/dashboard (nhẹ, không leak).
     */
    private function profileArray($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'gender' => $user->gender,
            'is_premium' => (bool) $user->is_premium,
            'premium_since' => $user->premium_since?->toIso8601String(),
            'language' => $user->language,
        ];
    }

    public function updateProfile(Request $request)
    {
        // TODO: Implement update profile
        return response()->json(['message' => 'Update profile functionality not implemented yet']);
    }

    public function updateAvatar(Request $request)
    {
        // TODO: Implement update avatar
        return response()->json(['message' => 'Update avatar functionality not implemented yet']);
    }
}
