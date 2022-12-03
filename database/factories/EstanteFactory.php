<?php

namespace Database\Factories;

use App\Models\Estante;
use App\Models\Sala;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estante>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class EstanteFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Estante::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => fake()->unique()->bothify('######-????'),
            'descricao' => fake()->optional()->sentence(),
            'sala_id' => Sala::factory(),
        ];
    }
}
