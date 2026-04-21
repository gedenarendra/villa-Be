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
        $bookings = Booking::with('villa')->latest()->get();

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
        $validated = $request->validate([
            'villa_id' => 'required|exists:villas,id',
            'start_year' => 'required|integer|min:2024|max:2100',
            'end_year' => 'required|integer|after_or_equal:start_year|max:2100',
            'note' => 'nullable|string'
        ]);

        // Create a booking for the full range: Jan 1 [Start] to Dec 31 [End]
        $startDate = $validated['start_year'] . '-01-01';
        $endDate = $validated['end_year'] . '-12-31';

        // Check if overlaps with existing confirmed bookings
        $exists = Booking::where('villa_id', $validated['villa_id'])
            ->where('status', 'confirmed')
            ->where(function($query) use ($startDate, $endDate) {
                $query->where(function($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
                });
            })->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Rentang tahun tersebut sudah terisi atau memiliki jadwal yang bentrok.'
            ], 422);
        }

        $booking = Booking::create([
            'villa_id' => $validated['villa_id'],
            'customer_name' => 'ADMIN BLOCK',
            'customer_email' => 'admin@nara.com',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_price' => 0,
            'status' => 'confirmed',
            'note' => $validated['note'] ?? 'Manual block for ' . ($validated['start_year'] == $validated['end_year'] ? $validated['start_year'] : $validated['start_year'] . '-' . $validated['end_year'])
        ]);

        // Hapus cache katalog agar status ketersediaan langsung terupdate
        Cache::forget('villas_catalog');

        return response()->json([
            'success' => true,
            'message' => 'Tahun berhasil diblokir di database!',
            'data' => $booking
        ]);
    }
}
