<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->nullable()->constrained('sales_quotes')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('sales_customers')->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('status', 32);
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
