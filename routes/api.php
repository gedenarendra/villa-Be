<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VillaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;

// ==========================================
// AREA PUBLIK (Tidak butuh token)
// ==========================================
// 1. Tamu melihat katalog villa
Route::get('/villas', [VillaController::class, 'index']);

// Rute untuk detail villa (saat diklik)
Route::get('/villas/{id}', [VillaController::class, 'show']);
Route::get('/villas/{id}/availability', [BookingController::class, 'availability']);

// 2. Admin melakukan login (rate limited untuk mencegah brute-force)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');


// ==========================================
// AREA TERLINDUNGI (Wajib menyertakan Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. Admin Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // 2. Cek profil admin yang sedang login
    Route::get('/admin/profile', function (Request $request) {
        return $request->user();
    });
    
    // 3. CRUD Villa
    Route::post('/villas', [VillaController::class, 'store']);      // Tambah Villa
    Route::put('/villas/{id}', [VillaController::class, 'update']);   // Edit Villa
    Route::delete('/villas/{id}', [VillaController::class, 'destroy']); // Hapus Villa

    // 4. Manajemen Booking & Blokir Kalender
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings/block', [BookingController::class, 'block']);
});

// Endpoint sederhana untuk memastikan server tetap aktif
Route::get('/ping', function () {
    return response()->json(['status' => 'active', 'timestamp' => now()]);
});