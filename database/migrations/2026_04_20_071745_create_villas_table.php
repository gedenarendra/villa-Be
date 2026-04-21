<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
    {
        Schema::create('villas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable(); 
            $table->string('location')->nullable();
            $table->decimal('price_per_year', 15, 2); 
            $table->integer('max_guests')->default(1);
            
            // 3 Status ketersediaan
            $table->enum('status', ['available', 'fullbooked', 'partially_booked'])->default('available');
            
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('villas');
    }};