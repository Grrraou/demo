<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_lot_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'batch_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_lot_batches');
    }
};
