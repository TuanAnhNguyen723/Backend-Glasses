<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const ADMIN_EMAIL = 'admin@gmail.com';
    private const ADMIN_PASSWORD = '123';

    public function showLogin(): View|RedirectResponse
    {
        if ($this->isAdminAuthenticated()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (
            $credentials['email'] !== self::ADMIN_EMAIL
            || $credentials['password'] !== self::ADMIN_PASSWORD
        ) {
            return back()
                ->withErrors(['email' => 'Sai email hoặc mật khẩu admin.'])
                ->withInput($request->only('email'));
        }

        $admin = $this->ensureAdminAccount();

        Auth::login($admin, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function ensureAdminAccount(): User
    {
        $admin = User::firstOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Administrator',
                'password' => Hash::make(self::ADMIN_PASSWORD),
            ]
        );

        if (!Hash::check(self::ADMIN_PASSWORD, $admin->password)) {
            $admin->forceFill([
                'password' => Hash::make(self::ADMIN_PASSWORD),
            ])->save();
        }

        return $admin;
    }

    private function isAdminAuthenticated(): bool
    {
        return Auth::check() && Auth::user()?->email === self::ADMIN_EMAIL;
    }
}
