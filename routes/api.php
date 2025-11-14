<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\UserExportController;
use App\Http\Controllers\Api\UserGDPRDeleteController;
use App\Http\Controllers\Api\UserListController;
use App\Http\Controllers\Api\UserManagementController;
use App\Http\Controllers\Api\UsersAnalyticsController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Registration & Login
Route::post('/register', [AuthController::class, 'register'])->middleware('idempotency');
Route::post('/login',    [AuthController::class, 'login'])->name('login')->middleware('idempotency');
Route::post('/magic', [AuthController::class, 'sendMagicLink']);
Route::get('/magic/consume/{token}', [AuthController::class, 'consumeMagicLink']);

// Email verification (NO LOGIN REQUIRED)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

// Resend verification email
Route::post('/email/resend', [AuthController::class, 'resendVerification'])
    ->name('verification.send');

// Authenticated routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/2fa/setup',   [AuthController::class, 'setup2FA']);
    Route::post('/2fa/enable',  [AuthController::class, 'enable2FA']);
    Route::post('/2fa/disable', [AuthController::class, 'disable2FA']);

    Route::post('/orgs', [OrganizationController::class, 'create'])
    ->middleware('org.permission:users.invite');
    Route::get('/orgs', [OrganizationController::class, 'index']);

    Route::post('/orgs/{org}/invites/accept/{token}',
        [OrganizationController::class, 'acceptInvite']);


    Route::post('/orgs/{org}/add-member', [OrganizationController::class, 'addMember']);

    Route::get('/users/top-logins', [UsersAnalyticsController::class, 'topLogins'])
        ->middleware('auth:api', 'org.permission:analytics.read');
    Route::get('/users/inactive', [UsersAnalyticsController::class, 'inactive']);

    Route::post('/users/{id}/export', [UserExportController::class, 'export']);
    Route::get('/users/{id}/export/download', [UserExportController::class, 'download']);


    Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
    Route::post('/users/{id}/restore', [UserManagementController::class, 'restore']);


    Route::post('/users/gdpr/request-delete', [UserGDPRDeleteController::class, 'requestDelete']);

    Route::post('/users/gdpr/{id}/approve', [UserGDPRDeleteController::class, 'approve']);
    Route::post('/users/gdpr/{id}/reject',  [UserGDPRDeleteController::class, 'reject']);

    Route::get('/users', [UserListController::class, 'index']);


});
