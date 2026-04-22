<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Villa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\VillaImage;

class VillaController extends Controller
{
    /**
     * Menampilkan semua daftar villa (Katalog)
     */
    public function index()
    {
        $villas = Cache::remember('villas_catalog', 3600, function () {
            // Mengubah ke array agar lebih aman saat di-cache (menghindari __PHP_Incomplete_Class)
            return Villa::with('images')->get()->toArray();
        });

        return response()->json([
            'success' => true,
            'data' => $villas
        ]);
    }

    /**
     * Menampilkan detail satu villa
     */
    public function show($id)
    {
        $villa = Villa::with('images')->find($id);
        if (!$villa) {
            return response()->json(['success' => false, 'message' => 'Villa not found'], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $villa
        ]);
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price_per_year' => 'required|numeric',
            'max_guests' => 'required|integer',
            'status' => 'nullable|in:available,fullbooked,partially_booked',
            'location' => 'required|string|max:255',
            'image_url' => 'required|url', // Untuk tahap awal, kita kirim URL gambar dulu
        ]);

        // 2. Simpan Data Villa
        $villa = Villa::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price_per_year' => $validated['price_per_year'],
            'max_guests' => $validated['max_guests'],
            'status' => $validated['status'] ?? 'available',
            'location' => $validated['location'],
        ]);

        // 3. Simpan Gambar Utama
        $villa->images()->create([
            'image_url' => $validated['image_url'],
            'is_primary' => true
        ]);

        // Hapus cache agar data terbaru muncul
        Cache::forget('villas_catalog');

        return response()->json([
            'success' => true,
            'message' => 'Villa berhasil ditambahkan',
            'data' => $villa->load('images')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $villa = Villa::find($id);
        
        // Format error disamakan dengan format success agar seragam di sisi FE
        if (!$villa) {
            return response()->json([
                'success' => false,
                'message' => 'Villa not found'
            ], 404);
        }

        // Aturan 'sometimes|required' sangat cocok untuk method PATCH
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price_per_year' => 'sometimes|required|numeric',
            'max_guests' => 'sometimes|required|integer',
            'status' => 'sometimes|nullable|in:available,fullbooked,partially_booked',
            'location' => 'sometimes|required|string|max:255',
        ]);

        $villa->update($validated);

        // Hapus cache agar katalog langsung menampilkan data terbaru
        Cache::forget('villas_catalog');

        return response()->json([
            'success' => true,
            'message' => 'Villa berhasil diperbarui',
            'data' => $villa->load('images') // Load images agar FE tidak error jika merender ulang komponen
        ]);
    }

    public function destroy($id)
    {
        $villa = Villa::find($id);
        if (!$villa) return response()->json(['message' => 'Not Found'], 404);

        $villa->delete(); // Ini akan otomatis menghapus gambar karena 'cascade' di migration

        // Hapus cache
        Cache::forget('villas_catalog');

        return response()->json([
            'success' => true,
            'message' => 'Villa berhasil dihapus'
        ]);
    }

    /**
     * Hard Reset: Menghapus semua data villa, gambar, dan booking
     */
    public function hardReset()
    {
        try {
            DB::beginTransaction();

            // Hapus data secara berurutan untuk menghindari constraint error
            Booking::query()->delete();
            VillaImage::query()->delete();
            Villa::query()->delete();

            DB::commit();

            // Hapus cache
            Cache::forget('villas_catalog');

            return response()->json([
                'success' => true,
                'message' => 'Seluruh data berhasil dihapus total'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mereset data: ' . $e->getMessage()
            ], 500);
        }
    }
}