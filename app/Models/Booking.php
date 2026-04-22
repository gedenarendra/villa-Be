<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Booking extends Model
{
    protected $fillable = [
        'villa_id', 'customer_name', 'customer_email', 
        'start_date', 'end_date', 'total_price', 'status', 'note'
    ];

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }

    // Trigger otomatis untuk mensinkronisasi status Villa dan menghapus cache
    protected static function booted()
    {
        static::created(function ($booking) {
            if ($booking->villa) {
                $booking->villa->syncStatus();
                Cache::forget('villas_catalog');
            }
        });

        static::deleted(function ($booking) {
            if ($booking->villa) {
                $booking->villa->syncStatus();
                Cache::forget('villas_catalog');
            }
        });

        static::updated(function ($booking) {
            if ($booking->villa) {
                $booking->villa->syncStatus();
                Cache::forget('villas_catalog');
            }
        });
    }
}