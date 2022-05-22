<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Floor>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class FloorFactory extends Factory
{
    protected $model = Floor::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'number' => $this->faker->unique()->numberBetween(-100000, 100000),
            'description' => $this->faker->optional()->sentence(),
            'building_id' => Building::factory(),
        ];
    }
}
