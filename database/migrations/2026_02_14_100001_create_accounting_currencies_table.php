<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_currencies', function (Blueprint $table) {
            $table->id();
            $table->char('code', 3)->unique(); // ISO 4217
            $table->string('name', 100);
            $table->string('symbol', 10);
            $table->tinyInteger('decimal_places')->default(2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_currencies');
    }
};
