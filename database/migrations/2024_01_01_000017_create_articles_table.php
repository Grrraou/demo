<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owned_company_id')->constrained('owned_companies')->cascadeOnDelete();
            $table->foreignId('team_member_id')->constrained('team_members')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->json('keywords')->nullable(); // array of strings
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->boolean('draft')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['owned_company_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
