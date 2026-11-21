<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->wantsJson()) {
        return response()->json([
            'status' => 'operational',
            'name' => 'CheckPass Core REST API Engine',
            'version' => '1.0.0',
            'portals' => [
                'super_admin' => env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login',
                'resident_app' => env('RESIDENT_APP_URL', 'http://localhost:5173'),
                'api_ping' => url('/api/v1/ping'),
            ],
            'default_credentials' => [
                'super_admin' => [
                    'email' => 'admin@checkpass.com',
                    'password' => '123456',
                    'login_url' => env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login',
                ],
            ],
        ]);
    }

    if ($request->has('gateway') || $request->query('view') === 'gateway') {
        return view('welcome');
    }

    // Default browser visit: redirect straight to Super Admin login portal
    return redirect(env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login');
});

Route::get('/admin', function () {
    return redirect(env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login');
});

Route::get('/super-admin', function () {
    return redirect(env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login');
});

Route::get('/superadmin', function () {
    return redirect(env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login');
});

Route::get('/login', function () {
    return redirect(env('ADMIN_FRONTEND_URL', 'http://localhost:3100') . '/login');
});

Route::get('/gateway', function () {
    return view('welcome');
});
