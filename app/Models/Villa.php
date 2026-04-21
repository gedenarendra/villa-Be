<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Villa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'price_per_year', 
        'max_guests', 'status', 'location'
    ];

    public function images()
    {
        return $this->hasMany(VillaImage::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Fungsi untuk menghitung otomatis status berdasarkan 6 tahun ke depan
    public function syncStatus()
    {
        $currentYear = date('Y');
        $windowYears = 6; 
        
        $bookedYears = $this->bookings()
            ->where('status', 'confirmed')
            ->get()
            ->flatMap(function($b) {
                $start = date('Y', strtotime($b->start_date));
                $end = date('Y', strtotime($b->end_date));
                return range($start, $end);
            })
            ->unique()
            ->toArray();

        $bookedInWindow = 0;
        for ($i = 0; $i < $windowYears; $i++) {
            if (in_array($currentYear + $i, $bookedYears)) {
                $bookedInWindow++;
            }
        }

        if ($bookedInWindow === 0) {
            $newStatus = 'available';
        } elseif ($bookedInWindow >= $windowYears) {
            $newStatus = 'fullbooked';
        } else {
            $newStatus = 'partially_booked'; // Ada yang terisi, tapi belum penuh 6 tahun
        }

        $this->updateQuietly(['status' => $newStatus]);
    }
}