<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->string('name', 100); // e.g., "Standard VAT"
            $table->string('code', 20); // e.g., "VAT20"
            $table->decimal('rate', 8, 4); // e.g., 20.0000 for 20%
            $table->string('type', 20)->default('percentage'); // percentage, fixed
            $table->boolean('is_compound')->default(false);
            $table->boolean('is_recoverable')->default(true); // for purchase tax
            $table->boolean('is_active')->default(true);
            $table->foreignId('account_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['owned_company_id', 'is_active']);
            $table->unique(['owned_company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_tax_rates');
    }
};
