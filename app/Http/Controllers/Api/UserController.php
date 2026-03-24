<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        $avatarUrl = null;
        if ($user->avatar) {
            if (str_starts_with((string) $user->avatar, 'http')) {
                $avatarUrl = $user->avatar; // URL từ OAuth hoặc nguồn ngoài
            } elseif (str_starts_with((string) $user->avatar, 'local:')) {
                // Fallback local (khi B2 lỗi)
                $avatarUrl = url('/storage/' . substr($user->avatar, 6));
            } elseif (str_starts_with((string) $user->avatar, 'avatars/')) {
                // Path B2: dùng direct URL (bucket public, giống products)
                $endpoint = rtrim(config('filesystems.disks.backblaze.endpoint'), '/');
                $bucket = config('filesystems.disks.backblaze.bucket');
                $avatarUrl = $endpoint . '/' . $bucket . '/' . $user->avatar;
            } else {
                $avatarUrl = url('/storage/' . $user->avatar);
            }
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $avatarUrl,
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

        // Xóa ảnh cũ (B2 hoặc local)
        if ($user->avatar && ! str_starts_with((string) $user->avatar, 'http')) {
            if (str_starts_with((string) $user->avatar, 'local:')) {
                Storage::disk('public')->delete(substr($user->avatar, 6));
            } elseif (str_starts_with((string) $user->avatar, 'avatars/')) {
                Storage::disk('backblaze')->delete($user->avatar);
            } else {
                Storage::disk('public')->delete($user->avatar);
            }
        }

        // Upload lên B2 (giống products)
        $file = $request->file('avatar');
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = 'avatars/user-' . $user->id . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        try {
            $stored = Storage::disk('backblaze')->put($fileName, file_get_contents($file->getRealPath()));
            if ($stored === false) {
                throw new \RuntimeException('B2 put returned false - kiểm tra quyền Application Key với bucket Glasses');
            }
        } catch (\Throwable $e) {
            Log::error('Avatar B2 upload failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'path' => $fileName,
                'exception' => $e,
            ]);
            // Fallback: lưu local (storage/public) - dùng prefix khác để phân biệt B2
            $localPath = $file->store('avatars', 'public');
            $user->avatar = 'local:' . $localPath;
            $user->save();
            return response()->json([
                'message' => 'Cập nhật avatar thành công (lưu local). Upload B2 thất bại: ' . $e->getMessage(),
                'user' => $this->profileArray($user->fresh()),
            ]);
        }

        $user->avatar = $fileName;
        $user->save();

        return response()->json([
            'message' => 'Cập nhật avatar thành công.',
            'user' => $this->profileArray($user->fresh()),
        ]);
    }
}
