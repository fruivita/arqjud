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
            'stand' => $this->faker->optional()->numerify('####'),
            'shelf' => $this->faker->optional()->numerify('##'),
            'room_id' => Room::factory(),
        ];
    }
}
