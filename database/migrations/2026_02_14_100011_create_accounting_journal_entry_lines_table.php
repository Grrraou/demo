<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('accounting_journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounting_accounts')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->decimal('debit', 18, 4)->default(0);
            $table->decimal('credit', 18, 4)->default(0);
            $table->decimal('debit_base', 18, 4)->default(0); // in base currency
            $table->decimal('credit_base', 18, 4)->default(0); // in base currency
            $table->foreignId('tax_rate_id')->nullable()->constrained('accounting_tax_rates')->nullOnDelete();
            $table->timestamps();

            $table->index('journal_entry_id');
            $table->index('account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_entry_lines');
    }
};
