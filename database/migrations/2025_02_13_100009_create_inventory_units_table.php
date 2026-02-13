<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');       // e.g. "Piece", "Kilogram"
            $table->string('symbol', 32); // e.g. "pcs", "kg"
            $table->timestamps();

            $table->unique('symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_units');
    }
};
