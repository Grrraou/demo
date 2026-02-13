<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $symbol = fake()->unique()->randomElement(['pcs', 'kg', 'box', 'unit', 'm', 'L', 'g', 'ml', 'cm']);
        $names = ['pcs' => 'Piece', 'kg' => 'Kilogram', 'box' => 'Box', 'unit' => 'Unit', 'm' => 'Metre', 'L' => 'Litre', 'g' => 'Gram', 'ml' => 'Millilitre', 'cm' => 'Centimetre'];

        return [
            'name' => $names[$symbol] ?? ucfirst($symbol),
            'symbol' => $symbol,
        ];
    }
}
