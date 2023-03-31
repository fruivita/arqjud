<?php

namespace Database\Factories;

use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\TipoProcesso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Caixa>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class CaixaFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Caixa::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => fake()->unique()->numberBetween(1, 9999999),
            'ano' => fake()->numberBetween(1900, 2020),
            'guarda_permanente' => fake()->boolean(),
            'complemento' => fake()->word(),
            'descricao' => fake()->optional()->sentence(),
            'prateleira_id' => Prateleira::factory(),
            'localidade_criadora_id' => Localidade::factory(),
            'tipo_processo_id' => TipoProcesso::factory(),
        ];
    }
}
