<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->string('code', 10); // e.g., "GJ", "SJ", "PJ"
            $table->string('name', 100); // General Journal, Sales Journal, etc.
            $table->string('type', 20); // general, sales, purchases, cash, bank
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['owned_company_id', 'code']);
            $table->index(['owned_company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journals');
    }
};
