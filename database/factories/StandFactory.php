<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\Stand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stand>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class StandFactory extends Factory
{
    protected $model = Stand::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'number' => $this->faker->unique()->numberBetween(),
            'description' => $this->faker->optional()->sentence(),
            'room_id' => Room::factory(),
        ];
    }
}
