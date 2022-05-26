<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Room>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'number' => $this->faker->unique()->numberBetween(),
            'description' => $this->faker->optional()->sentence(),
            'floor_id' => Floor::factory(),
        ];
    }
}
