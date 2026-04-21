<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'villa_id',
        'customer_name',
        'customer_email',
        'start_date',
        'end_date',
        'total_price',
        'status',
    ];

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
