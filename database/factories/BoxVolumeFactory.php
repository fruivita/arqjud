<?php

namespace Database\Factories;

use App\Models\Box;
use App\Models\BoxVolume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoxVolume>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class BoxVolumeFactory extends Factory
{
    protected $model = BoxVolume::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $number = $this->faker->unique()->numberBetween(1, 65535);

        return [
            'number' => $number,
            'alias' => "Vol. {$number}",
            'box_id' => Box::factory(),
        ];
    }
}
