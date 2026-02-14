<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_team_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('invited'); // invited, accepted, declined
            $table->timestamps();

            $table->unique(['event_id', 'team_member_id']);
            $table->index(['team_member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_team_member');
    }
};
