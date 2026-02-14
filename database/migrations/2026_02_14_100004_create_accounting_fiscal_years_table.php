<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->string('name', 50); // e.g., "FY 2026"
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open'); // open, closed, locked
            $table->timestamps();

            $table->index(['owned_company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_fiscal_years');
    }
};
