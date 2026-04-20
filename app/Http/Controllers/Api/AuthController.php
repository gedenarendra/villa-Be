<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Fungsi Login
    public function login(Request $request)
    {
        // 1. Validasi input dari React
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Cari admin berdasarkan email
        $admin = Admin::where('email', $request->email)->first();

        // 3. Cek apakah admin ada dan passwordnya cocok
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial salah atau tidak ditemukan.'
            ], 401);
        }

        // 4. Buat Token Akses
        $token = $admin->createToken('admin-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'admin' => $admin,
            'token' => $token
        ], 200);
    }

    // Fungsi Logout
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ], 200);
    }
}
