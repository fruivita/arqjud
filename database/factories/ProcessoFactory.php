<?php

namespace Database\Factories;

use App\Models\Processo;
use App\Models\VolumeCaixa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Processo>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class ProcessoFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Processo::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'processo_pai_id' => null,
            'volume_caixa_id' => VolumeCaixa::factory(),
            'numero' => fake()->unique()->numeroProcessoCNJ(),
            'numero_antigo' => fake()->unique()->numeroProcessoV1(),
            'arquivado_em' => fake()->date(),
            'guarda_permanente' => fake()->boolean(),
            'qtd_volumes' => fake()->numberBetween(1, 9999),
            'descricao' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Gera o número do processo no padrão 10 dígitos.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function v1()
    {
        return $this->state(function () {
            return [
                'numero' => fake()->unique()->numeroProcessoV1(),
            ];
        });
    }

    /**
     * Gera o número do processo no padrão 15 dígitos.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function v2()
    {
        return $this->state(function () {
            return [
                'numero' => fake()->unique()->numeroProcessoV2(),
            ];
        });
    }
}
