<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_country_configs', function (Blueprint $table) {
            $table->id();
            $table->char('country_code', 2)->unique();
            $table->string('name', 100);
            $table->char('default_currency', 3);
            $table->string('accounting_standard', 20)->nullable();
            $table->string('tax_name', 50)->default('Tax'); // "VAT", "GST", "Sales Tax"
            $table->string('config_class', 255)->nullable(); // PHP class for rules
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accounting_country_tax_rates', function (Blueprint $table) {
            $table->id();
            $table->char('country_code', 2);
            $table->string('name', 100);
            $table->string('code', 20);
            $table->decimal('rate', 8, 4);
            $table->string('category', 50)->default('standard'); // standard, reduced, zero, exempt
            $table->date('valid_from');
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->index(['country_code', 'category']);
            $table->foreign('country_code')->references('country_code')->on('accounting_country_configs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_country_tax_rates');
        Schema::dropIfExists('accounting_country_configs');
    }
};
