<?php

namespace Database\Factories;

use App\Models\Stand;
use App\Models\Shelf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shelf>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'number' => $this->faker->unique()->numberBetween(),
            'description' => $this->faker->optional()->sentence(),
            'stand_id' => Stand::factory(),
        ];
    }
}
