<?php

namespace Database\Factories;

use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Predio>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class PredioFactory extends Factory
{
    protected $model = Predio::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'nome' => $this->faker->unique()->text(20),
            'descricao' => $this->faker->optional()->sentence(),
            'localidade_id' => Localidade::factory(),
        ];
    }
}
