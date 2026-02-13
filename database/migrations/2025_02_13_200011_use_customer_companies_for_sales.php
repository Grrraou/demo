<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sales_quotes', 'sales_orders', 'sales_invoices'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('customer_company_id')->after('id')->nullable()->constrained('customer_companies')->nullOnDelete();
            });
        }

        Schema::dropIfExists('sales_customers');
    }

    public function down(): void
    {
        Schema::create('sales_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        foreach (['sales_quotes', 'sales_orders', 'sales_invoices'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['customer_company_id']);
                $table->dropColumn('customer_company_id');
            });
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('customer_id')->nullable()->after('id')->constrained('sales_customers')->nullOnDelete();
            });
        }
    }
};
