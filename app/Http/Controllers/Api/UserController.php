<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            'avatar' => $user->avatar
                ? (str_starts_with((string) $user->avatar, 'http') ? $user->avatar : url('/storage/' . $user->avatar))
                : null,
            'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
            'gender' => $user->gender,
            'is_premium' => (bool) $user->is_premium,
            'premium_since' => $user->premium_since?->toIso8601String(),
            'language' => $user->language,
        ];
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female,other',
            'language' => 'nullable|string|max:10',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $allowed = ['name', 'email', 'phone', 'date_of_birth', 'gender', 'language'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $validated)) {
                $user->{$field} = $validated[$field];
            }
        }

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Cập nhật thông tin thành công.',
            'user' => $this->profileArray($user->fresh()),
        ]);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        // Xóa ảnh cũ nếu có (path dạng avatars/xxx)
        if ($user->avatar && ! str_starts_with((string) $user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $file = $request->file('avatar');
        $path = $file->store('avatars', 'public');
        $user->avatar = $path; // Lưu path, profileArray sẽ convert sang URL
        $user->save();

        return response()->json([
            'message' => 'Cập nhật avatar thành công.',
            'user' => $this->profileArray($user->fresh()),
        ]);
    }
}
