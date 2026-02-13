<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_company_owned_company', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owned_company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_company_id', 'owned_company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_company_owned_company');
    }
};
