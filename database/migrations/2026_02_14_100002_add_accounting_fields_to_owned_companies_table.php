<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owned_companies', function (Blueprint $table) {
            $table->char('country_code', 2)->nullable()->after('logo');
            $table->char('currency_code', 3)->default('USD')->after('country_code');
            $table->tinyInteger('fiscal_year_start_month')->default(1)->after('currency_code');
            $table->string('accounting_standard', 20)->nullable()->after('fiscal_year_start_month');
            $table->boolean('tax_enabled')->default(true)->after('accounting_standard');
        });
    }

    public function down(): void
    {
        Schema::table('owned_companies', function (Blueprint $table) {
            $table->dropColumn([
                'country_code',
                'currency_code',
                'fiscal_year_start_month',
                'accounting_standard',
                'tax_enabled',
            ]);
        });
    }
};
