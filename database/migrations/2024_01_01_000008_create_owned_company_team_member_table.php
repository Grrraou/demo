<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owned_company_team_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owned_company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['team_member_id', 'owned_company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owned_company_team_member');
    }
};
