<?php

namespace Database\Factories;

use App\Models\Box;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Box>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class BoxFactory extends Factory
{
    protected $model = Box::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'number' => $this->faker->unique()->numberBetween(),
            'year' => $this->faker->numberBetween(1900, 2020),
            'stand' => $this->faker->optional()->numberBetween(1000, 9999),
            'shelf' => $this->faker->optional()->numberBetween(10, 99),
            'description' => $this->faker->optional()->sentence(),
            'room_id' => Room::factory(),
        ];
    }
}
