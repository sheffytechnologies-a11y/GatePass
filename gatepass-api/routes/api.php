<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PassController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\EmergencyController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes – Gatepass (Gatepass)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1
|
| Auth:   POST /api/v1/auth/login      → get Sanctum token
|         POST /api/v1/auth/logout     → revoke token (auth required)
|         GET  /api/v1/auth/me         → get authenticated resident
|         POST /api/v1/auth/change-password
|
| Passes: GET    /api/v1/passes
|         POST   /api/v1/passes
|         GET    /api/v1/passes/{ulid}
|         PATCH  /api/v1/passes/{ulid}/revoke
|         PATCH  /api/v1/passes/{ulid}/extend
|         POST   /api/v1/passes/{ulid}/flag-item
|
| Notifications: GET    /api/v1/notifications
|                PATCH  /api/v1/notifications/read-all
|                PATCH  /api/v1/notifications/{id}/read
|
| Home:       GET /api/v1/home/summary
|
| Emergencies: GET  /api/v1/emergencies
|              POST /api/v1/emergencies
|
| Profile:    GET   /api/v1/profile
|             PATCH /api/v1/profile
|             PATCH /api/v1/profile/preferences
|
*/

// ── Top-level shorthand (base URL: /api) ──────────────────────────────────
Route::post('login', [AuthController::class, 'login']);

Route::prefix('v1')->group(function () {

    // ── Public auth routes ─────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // ── Protected routes (Sanctum token required) ──────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('logout',          [AuthController::class, 'logout']);
            Route::get('me',               [AuthController::class, 'me']);
            Route::post('change-password', [AuthController::class, 'changePassword']);
        });

        // Home summary
        Route::get('home/summary', [HomeController::class, 'summary']);

        // Passes
        Route::prefix('passes')->group(function () {
            Route::get('/',               [PassController::class, 'index']);
            Route::post('/',              [PassController::class, 'store']);
            Route::get('{ulid}',          [PassController::class, 'show']);
            Route::patch('{ulid}/revoke', [PassController::class, 'revoke']);
            Route::patch('{ulid}/extend', [PassController::class, 'extend']);
            Route::post('{ulid}/flag-item', [PassController::class, 'flagItem']);
            // Items
            Route::get('{ulid}/items',         [ItemController::class, 'index']);
            Route::post('{ulid}/items',         [ItemController::class, 'store']);
            Route::get('{ulid}/items/{id}',     [ItemController::class, 'show']);
            Route::patch('{ulid}/items/{id}',   [ItemController::class, 'update']);
            Route::delete('{ulid}/items/{id}',  [ItemController::class, 'destroy']);
        });

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/',              [NotificationController::class, 'index']);
            Route::patch('read-all',     [NotificationController::class, 'markAllRead']);
            Route::patch('{id}/read',    [NotificationController::class, 'markOneRead']);
        });

        // Emergency
        Route::prefix('emergencies')->group(function () {
            Route::get('/',  [EmergencyController::class, 'index']);
            Route::post('/', [EmergencyController::class, 'store']);
        });

        // Profile
        Route::prefix('profile')->group(function () {
            Route::get('/',           [ProfileController::class, 'show']);
            Route::patch('/',         [ProfileController::class, 'update']);
            Route::patch('preferences', [ProfileController::class, 'updatePreferences']);
        });

        // ── Admin routes (security users only) ────────────────────────────
        Route::prefix('admin')->group(function () {
            Route::get('dashboard', [AdminController::class, 'dashboard']);

            // Users
            Route::get('users',         [AdminController::class, 'listUsers']);
            Route::post('users',        [AdminController::class, 'createUser']);
            Route::get('users/{id}',    [AdminController::class, 'showUser']);
            Route::patch('users/{id}',  [AdminController::class, 'updateUser']);
            Route::delete('users/{id}', [AdminController::class, 'deleteUser']);

            // Residents
            Route::get('residents',         [AdminController::class, 'listResidents']);
            Route::post('residents',        [AdminController::class, 'createResident']);
            Route::get('residents/{id}',    [AdminController::class, 'showResident']);
            Route::patch('residents/{id}',  [AdminController::class, 'updateResident']);
            Route::delete('residents/{id}', [AdminController::class, 'deleteResident']);

            // Passes
            Route::get('passes',              [AdminController::class, 'listPasses']);
            Route::post('passes',             [AdminController::class, 'createPass']);
            Route::get('passes/{ulid}',       [AdminController::class, 'showPass']);
            Route::patch('passes/{ulid}/revoke', [AdminController::class, 'revokePass']);
            Route::delete('passes/{ulid}',    [AdminController::class, 'deletePass']);

            // Emergencies
            Route::get('emergencies',                        [AdminController::class, 'listEmergencies']);
            Route::patch('emergencies/{id}/acknowledge',     [AdminController::class, 'acknowledgeEmergency']);
            Route::patch('emergencies/{id}/resolve',         [AdminController::class, 'resolveEmergency']);
            Route::delete('emergencies/{id}',                [AdminController::class, 'deleteEmergency']);

            // Notifications
            Route::get('notifications',         [AdminController::class, 'listNotifications']);
            Route::delete('notifications/{id}', [AdminController::class, 'deleteNotification']);

            // Estates & Units (reference data)
            Route::get('estates',                [AdminController::class, 'listEstates']);
            Route::get('estates/{id}/units',     [AdminController::class, 'listUnits']);
        });
    });
});
