<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->enum('status', ['available', 'not available'])->default('available')->after('max_guests');
            $table->string('location')->nullable()->after('status');
            $table->renameColumn('price_per_night', 'price_per_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->dropColumn(['status', 'location']);
            $table->renameColumn('price_per_year', 'price_per_night');
        });
    }
};
