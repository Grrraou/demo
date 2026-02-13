<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Inventory\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'name' => fake()->words(3, true),
            'description' => fake()->optional(0.7)->sentence(),
            'unit_id' => Unit::factory(),
            'category_id' => null,
        ];
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => ['category_id' => $category->id]);
    }
}
