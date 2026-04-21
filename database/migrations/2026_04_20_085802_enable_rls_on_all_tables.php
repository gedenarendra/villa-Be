<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'admins',
            'villas',
            'villa_images',
            'bookings',
            'personal_access_tokens',
            'migrations'
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE \"$table\" ENABLE ROW LEVEL SECURITY;");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'admins',
            'villas',
            'villa_images',
            'bookings',
            'personal_access_tokens',
            'migrations'
        ];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE \"$table\" DISABLE ROW LEVEL SECURITY;");
        }
    }
};
