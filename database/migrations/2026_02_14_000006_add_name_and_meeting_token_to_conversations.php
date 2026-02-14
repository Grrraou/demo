<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('meeting_token', 64)->nullable()->after('entity_id');
        });

        // Generate meeting tokens for existing conversations
        DB::table('conversations')->whereNull('meeting_token')->cursor()->each(function ($conversation) {
            DB::table('conversations')
                ->where('id', $conversation->id)
                ->update(['meeting_token' => Str::random(32)]);
        });

        // Make meeting_token not nullable after populating
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('meeting_token', 64)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['name', 'meeting_token']);
        });
    }
};
