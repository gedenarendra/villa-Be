<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Villa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price_per_year',
        'max_guests',
        'status',
        'location', // Adding location
    ];

    public function images()
    {
        return $this->hasMany(VillaImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Cek apakah villa sedang terisi hari ini
     */
    public function isOccupied($date = null)
    {
        $date = $date ?: now()->toDateString();
        return $this->bookings()
            ->where('status', 'confirmed')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    public function getAvailabilityStatus($date = null)
    {
        // 0. Cek status manual dari database
        if ($this->status === 'not available') {
            return 'fully booked';
        }

        $today = $date ?: now()->toDateString();
        $currentYear = \Illuminate\Support\Carbon::parse($today)->year;
        $windowYears = 6;
        
        // Gunakan bookings yang sudah di-load jika ada (Eager Loading)
        $bookings = $this->relationLoaded('bookings') ? $this->bookings : $this->bookings()
            ->where('status', 'confirmed')
            ->where('end_date', '>=', $today)
            ->get();

        // 1. Cek apakah terisi hari ini
        $isOccupiedToday = $bookings->some(function($b) use ($today) {
            return $b->status === 'confirmed' && $b->start_date <= $today && $b->end_date >= $today;
        });

        if (!$isOccupiedToday) {
            return 'available';
        }

        // 2. Jika hari ini terisi, cek jendela 6 tahun
        $bookedYears = $bookings->flatMap(function($b) {
            $start = \Illuminate\Support\Carbon::parse($b->start_date)->year;
            $end = \Illuminate\Support\Carbon::parse($b->end_date)->year;
            return range($start, $end);
        })->unique()->toArray();

        $allOccupied = true;
        for ($i = 0; $i < $windowYears; $i++) {
            if (!in_array($currentYear + $i, $bookedYears)) {
                $allOccupied = false;
                break;
            }
        }

        return $allOccupied ? 'fully booked' : 'limited';
    }

    /**
     * Hitung tahun ketersediaan berikutnya
     */
    public function getNextAvailableYear()
    {
        $currentYear = now()->year;
        $bookedYears = $this->bookings()
            ->where('status', 'confirmed')
            ->where('end_date', '>=', now()->toDateString())
            ->get()
            ->flatMap(function($booking) {
                $start = \Illuminate\Support\Carbon::parse($booking->start_date)->year;
                $end = \Illuminate\Support\Carbon::parse($booking->end_date)->year;
                return range($start, $end);
            })
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        $nextYear = $currentYear + 1;
        while (in_array($nextYear, $bookedYears)) {
            $nextYear++;
        }
        return $nextYear;
    }
}
