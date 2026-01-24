<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
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
