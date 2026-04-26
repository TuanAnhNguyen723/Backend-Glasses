<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuthenticate::class,
        ]);

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Thêm CORS vào response lỗi từ API (khi 500/fatal, response vẫn có header để frontend không bị chặn CORS)
        $exceptions->respond(function ($response, $e, $request) {
            if ($request->is('api/*') && method_exists($response, 'headers')) {
                $origins = config('cors.allowed_origins', []);
                $origin = $request->header('Origin');
                if ($origin && in_array($origin, $origins)) {
                    $response->headers->set('Access-Control-Allow-Origin', $origin);
                } elseif (!empty($origins)) {
                    $response->headers->set('Access-Control-Allow-Origin', $origins[0]);
                }
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $methods = config('cors.allowed_methods', ['*']);
                $response->headers->set('Access-Control-Allow-Methods', is_array($methods) ? implode(', ', $methods) : $methods);
                $headers = config('cors.allowed_headers', ['*']);
                $response->headers->set('Access-Control-Allow-Headers', is_array($headers) ? implode(', ', $headers) : $headers);
            }
            return $response;
        });
    })->create();
