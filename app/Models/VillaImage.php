<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VillaImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'villa_id',
        'image_url',
        'is_primary',
    ];

    public function villa()
    {
        return $this->belongsTo(Villa::class);
    }
}
