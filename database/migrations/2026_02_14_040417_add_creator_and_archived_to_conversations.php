<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('team_members')->nullOnDelete();
            $table->timestamp('archived_at')->nullable()->after('meeting_token');
        });

        // Set created_by to the first participant for existing conversations
        DB::statement('
            UPDATE conversations 
            SET created_by = (
                SELECT team_member_id 
                FROM conversation_team_member 
                WHERE conversation_team_member.conversation_id = conversations.id 
                ORDER BY conversation_team_member.created_at ASC 
                LIMIT 1
            )
            WHERE created_by IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['created_by', 'archived_at']);
        });
    }
};
