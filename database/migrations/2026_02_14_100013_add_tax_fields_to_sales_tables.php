<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add tax fields to sales_invoices
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->char('currency_code', 3)->default('USD')->after('notes');
            $table->decimal('exchange_rate', 18, 8)->default(1)->after('currency_code');
            $table->decimal('subtotal', 18, 2)->default(0)->after('exchange_rate');
            $table->decimal('tax_total', 18, 2)->default(0)->after('subtotal');
            $table->decimal('total', 18, 2)->default(0)->after('tax_total');
            $table->foreignId('journal_entry_id')->nullable()->after('total')
                ->constrained('accounting_journal_entries')->nullOnDelete();
        });

        // Add tax fields to sales_invoice_items
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')->nullable()->after('unit_price')
                ->constrained('accounting_tax_rates')->nullOnDelete();
            $table->decimal('tax_amount', 14, 2)->default(0)->after('tax_rate_id');
            $table->decimal('subtotal', 14, 2)->default(0)->after('tax_amount');
            $table->decimal('line_total', 14, 2)->default(0)->after('subtotal');
        });

        // Add tax fields to sales_quotes
        Schema::table('sales_quotes', function (Blueprint $table) {
            $table->char('currency_code', 3)->default('USD')->after('notes');
            $table->decimal('subtotal', 18, 2)->default(0)->after('currency_code');
            $table->decimal('tax_total', 18, 2)->default(0)->after('subtotal');
            $table->decimal('total', 18, 2)->default(0)->after('tax_total');
        });

        // Add tax fields to sales_quote_items
        Schema::table('sales_quote_items', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')->nullable()->after('unit_price')
                ->constrained('accounting_tax_rates')->nullOnDelete();
            $table->decimal('tax_amount', 14, 2)->default(0)->after('tax_rate_id');
            $table->decimal('subtotal', 14, 2)->default(0)->after('tax_amount');
            $table->decimal('line_total', 14, 2)->default(0)->after('subtotal');
        });

        // Add tax fields to sales_orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->char('currency_code', 3)->default('USD')->after('notes');
            $table->decimal('subtotal', 18, 2)->default(0)->after('currency_code');
            $table->decimal('tax_total', 18, 2)->default(0)->after('subtotal');
            $table->decimal('total', 18, 2)->default(0)->after('tax_total');
        });

        // Add tax fields to sales_order_items
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->foreignId('tax_rate_id')->nullable()->after('unit_price')
                ->constrained('accounting_tax_rates')->nullOnDelete();
            $table->decimal('tax_amount', 14, 2)->default(0)->after('tax_rate_id');
            $table->decimal('subtotal', 14, 2)->default(0)->after('tax_amount');
            $table->decimal('line_total', 14, 2)->default(0)->after('subtotal');
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn(['tax_rate_id', 'tax_amount', 'subtotal', 'line_total']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'subtotal', 'tax_total', 'total']);
        });

        Schema::table('sales_quote_items', function (Blueprint $table) {
            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn(['tax_rate_id', 'tax_amount', 'subtotal', 'line_total']);
        });

        Schema::table('sales_quotes', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'subtotal', 'tax_total', 'total']);
        });

        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['tax_rate_id']);
            $table->dropColumn(['tax_rate_id', 'tax_amount', 'subtotal', 'line_total']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['currency_code', 'exchange_rate', 'subtotal', 'tax_total', 'total', 'journal_entry_id']);
        });
    }
};
