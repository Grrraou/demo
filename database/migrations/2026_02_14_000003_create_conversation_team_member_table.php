<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_team_member', function (Blueprint $table) {
            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('team_member_id')
                ->constrained('team_members')
                ->cascadeOnDelete();
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->primary(['conversation_id', 'team_member_id']);
            $table->index('team_member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_team_member');
    }
};
