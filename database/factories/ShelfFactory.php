<?php

namespace Database\Factories;

use App\Models\Shelf;
use App\Models\Stand;
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
        $number = $this->faker->unique()->numberBetween();

        return [
            'number' => $number,
            'alias' => $number,
            'description' => $this->faker->optional()->sentence(),
            'stand_id' => Stand::factory(),
        ];
    }
}
