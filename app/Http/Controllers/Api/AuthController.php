<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

use App\Http\Resources\AdminResource;
class AuthController extends Controller
{
    // Fungsi Login
    public function login(LoginRequest $request)
    {
        // 2. Cari admin berdasarkan email
        $admin = Admin::where('email', $request->email)->first();

        // 3. Cek apakah admin ada dan passwordnya cocok
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial salah atau tidak ditemukan.'
            ], 401);
        }

        // 4. Buat Token Akses dengan ability khusus
        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'admin' => new AdminResource($admin),
            'token' => $token
        ], 200);
    }

    // Fungsi Logout
    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan (null-safe)
        optional($request->user()->currentAccessToken())->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ], 200);
    }
}
