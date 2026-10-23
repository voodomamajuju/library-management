<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\BorrowingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Middleware\EnsureAdminUser;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MemberDashboardController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Member authentication (separate from the admin/library login flow).
Route::post('/member/register', [MemberAuthController::class, 'register']);
Route::post('/member/login', [MemberAuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/member/logout', [MemberAuthController::class, 'logout']);
    Route::get('/member/me', [MemberAuthController::class, 'me']);
    Route::get('/member/dashboard', [MemberDashboardController::class, 'index']);
});

// Admin/library API. EnsureAdminUser keeps member tokens out of admin endpoints;
// admin (User) tokens behave exactly as before.
Route::middleware(['auth:sanctum', EnsureAdminUser::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::post('/books/import', [BookController::class, 'import']);
    Route::post('/books', [BookController::class, 'store']);
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::put('/books/{book}', [BookController::class, 'update']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);
    Route::post('/members/import', [MemberController::class, 'import']);
    Route::post('/members', [MemberController::class, 'store']);
    Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{member}', [MemberController::class, 'show']);
    Route::put('/members/{member}', [MemberController::class, 'update']);
    Route::delete('/members/{member}', [MemberController::class, 'destroy']);

    Route::post('/borrowings', [BorrowingController::class, 'store']);
    Route::get('/borrowings', [BorrowingController::class, 'index']);
    Route::get('/borrowings/{borrowing}', [BorrowingController::class, 'show']);
    Route::post('/borrowings/{borrowing}/return', [BorrowingController::class, 'returnBook']);
});