<?php

namespace Database\Factories;

use App\Models\Lotacao;
use FruiVita\Corporativo\Database\Factories\LotacaoFactory as LotacaoCorporativoFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lotacao>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class LotacaoFactory extends Factory
{
    protected $model = Lotacao::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return
        // sobrescreve a regra de geração do id
        ['id' => $this->faker->unique()->numberBetween(1)]
        + (new LotacaoCorporativoFactory())->definition();
    }
}
