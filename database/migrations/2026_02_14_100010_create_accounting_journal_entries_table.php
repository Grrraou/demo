<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_id')->constrained('accounting_journals')->onDelete('restrict');
            $table->foreignId('fiscal_year_id')->constrained('accounting_fiscal_years')->onDelete('restrict');
            $table->string('entry_number', 50);
            $table->date('entry_date');
            $table->string('reference', 100)->nullable(); // e.g., invoice number
            $table->text('description')->nullable();
            $table->nullableMorphs('source'); // source_type, source_id for polymorphic relation
            $table->char('currency_code', 3)->default('USD');
            $table->decimal('exchange_rate', 18, 8)->default(1);
            $table->string('status', 20)->default('draft'); // draft, posted, reversed
            $table->timestamp('posted_at')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('team_members')->nullOnDelete();
            $table->foreignId('reversed_by_id')->nullable()->constrained('accounting_journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('team_members')->nullOnDelete();
            $table->timestamps();

            $table->unique(['owned_company_id', 'entry_number']);
            $table->index(['owned_company_id', 'entry_date']);
            $table->index(['owned_company_id', 'status']);
            // source_type and source_id index is created by nullableMorphs
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_journal_entries');
    }
};
