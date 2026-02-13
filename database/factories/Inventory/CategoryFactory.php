<?php

namespace Database\Factories\Inventory;

use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'parent_id' => null,
        ];
    }

    public function childOf(?Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent?->id,
        ]);
    }
}
