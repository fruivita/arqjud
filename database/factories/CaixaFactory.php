<?php

namespace Database\Factories;

use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Prateleira;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Caixa>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class CaixaFactory extends Factory
{
    protected $model = Caixa::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => $this->faker->unique()->numberBetween(),
            'ano' => $this->faker->numberBetween(1900, 2020),
            'guarda_permanente' => $this->faker->boolean(),
            'complemento' => $this->faker->optional()->word(),
            'descricao' => $this->faker->optional()->sentence(),
            'prateleira_id' => Prateleira::factory(),
            'localidade_criadora_id' => Localidade::factory(),
        ];
    }
}
