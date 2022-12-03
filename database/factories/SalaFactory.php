<?php

namespace Database\Factories;

use App\Models\Andar;
use App\Models\Sala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Sala>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class SalaFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Sala::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => fake()->unique()->bothify('#####-???'),
            'descricao' => fake()->optional()->sentence(),
            'andar_id' => Andar::factory(),
        ];
    }
}
