<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->string('code', 20);
            $table->string('name', 255);
            $table->string('type', 20); // asset, liability, equity, revenue, expense
            $table->string('subtype', 50)->nullable(); // current_asset, fixed_asset, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // prevents deletion
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['owned_company_id', 'code']);
            $table->index(['owned_company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_accounts');
    }
};
