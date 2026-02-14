<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('started_by')
                ->constrained('team_members')
                ->cascadeOnDelete();
            $table->string('room_name');
            $table->string('room_token', 64);
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'ended_at']);
        });

        // Add meeting_id to messages for meeting-related messages
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('meeting_id')
                ->nullable()
                ->constrained('chat_meetings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meeting_id');
        });

        Schema::dropIfExists('chat_meetings');
    }
};
