<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Villa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    /**
     * Get all bookings with villa relationship (Admin)
     */
    public function index()
    {
        $bookings = Booking::with('villa')->orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Get availability dates for a specific villa (Public)
     */
    public function availability($id)
    {
        $bookings = Booking::where('villa_id', $id)
            ->where('status', '!=', 'cancelled')
            ->select('start_date', 'end_date', 'status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings
        ]);
    }

    /**
     * Admin: Manually block a range of years
     */
    public function block(Request $request) 
    {
        $request->validate([
            'villa_id' => 'required|exists:villas,id',
            'start_year' => 'required|integer',
            'end_year' => 'required|integer|gte:start_year',
            'note' => 'nullable|string'
        ]);

        // Konversi Tahun dari FE ke format Date (YYYY-MM-DD)
        $startDate = $request->start_year . '-01-01';
        $endDate = $request->end_year . '-12-31';

        // Insert ke tabel bookings
        $booking = Booking::create([
            'villa_id' => $request->villa_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'customer_name' => 'Admin System', // Menandakan ini blokir manual
            'note' => $request->note,
            'status' => 'confirmed'
        ]);

        // Relasi villa->syncStatus() sudah otomatis berjalan lewat Model Observer di atas

        return response()->json([
            'success' => true,
            'message' => 'Rentang tahun berhasil diblokir dan status properti diperbarui!',
            'data' => $booking
        ]);
    }
}
