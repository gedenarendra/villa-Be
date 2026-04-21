<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Trigger otomatis untuk mensinkronisasi status Villa
    protected static function booted()
    {
        static::created(function ($booking) {
            if($booking->villa) $booking->villa->syncStatus();
        });

        static::deleted(function ($booking) {
            if($booking->villa) $booking->villa->syncStatus();
        });

        static::updated(function ($booking) {
            if($booking->villa) $booking->villa->syncStatus();
        });
    }
}