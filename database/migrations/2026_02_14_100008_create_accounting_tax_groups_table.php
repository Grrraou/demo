<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_tax_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('owned_company_id');
        });

        Schema::create('accounting_tax_group_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_group_id')->constrained('accounting_tax_groups')->onDelete('cascade');
            $table->foreignId('tax_rate_id')->constrained('accounting_tax_rates')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['tax_group_id', 'tax_rate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_tax_group_rates');
        Schema::dropIfExists('accounting_tax_groups');
    }
};
