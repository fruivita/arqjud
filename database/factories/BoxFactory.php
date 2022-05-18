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
            'number' => $this->faker->unique()->numerify('######'),
            'year' => $this->faker->numberBetween(1900, 2020),
            'stand' => rand(0, 1)
                ? null
                : $this->faker->numerify('####'),

            'shelf' => rand(0, 1)
                ? null
                : $this->faker->numerify('##'),

            'room_id' => Room::factory(),
        ];
    }
}
