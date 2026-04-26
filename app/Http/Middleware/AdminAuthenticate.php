<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    private const ADMIN_EMAIL = 'admin@gmail.com';

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        if (Auth::user()?->email !== self::ADMIN_EMAIL) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => 'Tài khoản này không có quyền quản trị.',
            ]);
        }

        return $next($request);
    }
}
