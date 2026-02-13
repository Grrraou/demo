<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEMO_LOGOS = [
        'acme-corp' => '1-698e6945ae4bb.png',
        'globex-inc' => '2-698e695b4e774.png',
        'initech' => '3-698e6969c0db0.png',
    ];

    public function up(): void
    {
        foreach (self::DEMO_LOGOS as $slug => $logo) {
            DB::table('owned_companies')
                ->where('slug', $slug)
                ->update(['logo' => $logo, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::DEMO_LOGOS) as $slug) {
            DB::table('owned_companies')
                ->where('slug', $slug)
                ->update(['logo' => null, 'updated_at' => now()]);
        }
    }
};
