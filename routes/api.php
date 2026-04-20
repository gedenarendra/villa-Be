<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VillaController;
use App\Http\Controllers\Api\AuthController;

// ==========================================
// AREA PUBLIK (Tidak butuh token)
// ==========================================
// 1. Tamu melihat katalog villa
Route::get('/villas', [VillaController::class, 'index']);

// 2. Admin melakukan login
Route::post('/login', [AuthController::class, 'login']);


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

    // Nanti, fungsi seperti tambah/edit/hapus villa ditaruh di dalam area ini.
    // Contoh (jangan di-uncomment dulu karena fungsinya belum dibuat):
    // Route::post('/villas', [VillaController::class, 'store']);
    // Route::put('/villas/{id}', [VillaController::class, 'update']);
    // Route::delete('/villas/{id}', [VillaController::class, 'destroy']);
    
});

// Endpoint sederhana untuk memastikan server tetap aktif
Route::get('/ping', function () {
    return response()->json(['status' => 'active', 'timestamp' => now()]);
});