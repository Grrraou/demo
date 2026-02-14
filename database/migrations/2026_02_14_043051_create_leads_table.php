<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('status', 20)->default('new');
            $table->integer('position')->default(0);
            $table->decimal('value', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('team_members')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('team_members')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['owned_company_id', 'status']);
            $table->index(['owned_company_id', 'status', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
