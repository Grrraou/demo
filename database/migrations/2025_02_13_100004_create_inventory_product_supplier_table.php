<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_product_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('inventory_suppliers')->cascadeOnDelete();
            $table->decimal('cost_price', 14, 2)->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->decimal('minimum_order_qty', 14, 4)->default(1);
            $table->timestamps();

            $table->unique(['product_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_product_supplier');
    }
};
