<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Villa;

class VillaSeeder extends Seeder
{
    public function run(): void
    {
        $villas = [
            [
                'name' => 'Villa Nara Ocean Front',
                'description' => 'Luxury oceanfront villa with private infinity pool and stunning sunset views.',
                'price_per_year' => 1500000000,
                'max_guests' => 8,
                'status' => 'available',
                'location' => 'Uluwatu, Bali',
                'image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&q=80&w=1000'
            ],
            [
                'name' => 'The Emerald Sanctuary',
                'description' => 'Modern minimalist villa nestled in the lush green jungles of Ubud.',
                'price_per_year' => 850000000,
                'max_guests' => 4,
                'status' => 'available',
                'location' => 'Ubud, Bali',
                'image' => 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?auto=format&fit=crop&q=80&w=1000'
            ],
            [
                'name' => 'Azure Cliffside Estate',
                'description' => 'Exclusive estate perched on a cliff with 270-degree ocean views.',
                'price_per_year' => 2200000000,
                'max_guests' => 12,
                'status' => 'available',
                'location' => 'Canggu, Bali',
                'image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&q=80&w=1000'
            ]
        ];

        foreach ($villas as $v) {
            $villa = Villa::create([
                'name' => $v['name'],
                'description' => $v['description'],
                'price_per_year' => $v['price_per_year'],
                'max_guests' => $v['max_guests'],
                'status' => $v['status'],
                'location' => $v['location'],
            ]);

            $villa->images()->create([
                'image_url' => $v['image'],
                'is_primary' => true
            ]);
        }
    }
}
