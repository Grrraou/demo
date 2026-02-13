<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('description')->constrained('inventory_units')->nullOnDelete();
        });

        // Ensure default units exist for backfill
        $defaultUnits = [
            ['name' => 'Piece', 'symbol' => 'pcs'],
            ['name' => 'Kilogram', 'symbol' => 'kg'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Unit', 'symbol' => 'unit'],
            ['name' => 'Metre', 'symbol' => 'm'],
            ['name' => 'Litre', 'symbol' => 'L'],
        ];
        $now = now();
        foreach ($defaultUnits as $u) {
            if (! DB::table('inventory_units')->where('symbol', $u['symbol'])->exists()) {
                DB::table('inventory_units')->insert([
                    'name' => $u['name'],
                    'symbol' => $u['symbol'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Backfill unit_id from unit string (match by symbol in inventory_units)
        $units = DB::table('inventory_units')->pluck('id', 'symbol');
        foreach (DB::table('inventory_products')->whereNotNull('unit')->get() as $row) {
            $unitId = $units[$row->unit] ?? $units['pcs'] ?? null;
            if ($unitId) {
                DB::table('inventory_products')->where('id', $row->id)->update(['unit_id' => $unitId]);
            }
        }

        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->string('unit', 32)->default('pcs')->after('description');
        });
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};
