<?php

namespace Database\Factories;

use App\Models\Andar;
use App\Models\Sala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sala>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class SalaFactory extends Factory
{
    protected $model = Sala::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => $this->faker->unique()->numberBetween(),
            'descricao' => $this->faker->optional()->sentence(),
            'andar_id' => Andar::factory(),
        ];
    }
}
