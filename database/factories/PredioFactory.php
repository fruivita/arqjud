<?php

namespace Database\Factories;

use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Predio>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class PredioFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Predio::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'nome' => fake()->unique()->text(40),
            'descricao' => fake()->optional()->sentence(),
            'localidade_id' => Localidade::factory(),
        ];
    }
}
