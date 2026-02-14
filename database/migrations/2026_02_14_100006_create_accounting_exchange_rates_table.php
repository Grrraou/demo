<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->char('from_currency', 3);
            $table->char('to_currency', 3);
            $table->decimal('rate', 18, 8);
            $table->date('effective_date');
            $table->timestamps();

            $table->index(['owned_company_id', 'from_currency', 'to_currency', 'effective_date'], 'exchange_rate_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_exchange_rates');
    }
};
