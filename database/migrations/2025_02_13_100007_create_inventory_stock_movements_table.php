<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('inventory_stocks')->cascadeOnDelete();
            $table->string('type', 32); // entry, exit, transfer
            $table->decimal('quantity', 14, 4); // positive for entry, negative for exit
            $table->unsignedBigInteger('reference_id')->nullable(); // optional link to purchase/sale/etc.
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['stock_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_movements');
    }
};
