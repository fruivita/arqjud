<?php

namespace Database\Factories;

use App\Models\Guia;
use App\Models\Lotacao;
use App\Models\Processo;
use App\Models\Solicitacao;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Solicitacao>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class SolicitacaoFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Solicitacao::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'solicitada_em' => now()->subWeeks(rand(21, 30)),
            'entregue_em' => now()->subWeeks(rand(11, 20)),
            'devolvida_em' => now()->subWeeks(rand(1, 10)),
            'por_guia' => fake()->boolean(),
            'descricao' => fake()->optional()->sentence(),
            'processo_id' => Processo::factory(),
            'solicitante_id' => Usuario::factory(),
            'recebedor_id' => Usuario::factory(),
            'remetente_id' => Usuario::factory(),
            'rearquivador_id' => Usuario::factory(),
            'destino_id' => Lotacao::factory(),
            'guia_id' => Guia::factory(),
        ];
    }

    /**
     * Gera uma solicitação no status solicitada.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function solicitada()
    {
        return $this->state(function () {
            $lotacao = Lotacao::factory()->create();

            return [
                'entregue_em' => null,
                'devolvida_em' => null,
                'por_guia' => false,
                'solicitante_id' => Usuario::factory()->for($lotacao, 'lotacao'),
                'recebedor_id' => null,
                'remetente_id' => null,
                'rearquivador_id' => null,
                'destino_id' => $lotacao->id,
                'guia_id' => null,
            ];
        });
    }

    /**
     * Gera uma solicitação no status entregue, isto é, ainda não devolvida.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function entregue()
    {
        return $this->state(function () {
            $lotacao = Lotacao::factory()->create();

            return [
                'devolvida_em' => null,
                'solicitante_id' => Usuario::factory()->for($lotacao, 'lotacao'),
                'recebedor_id' => Usuario::factory()->for($lotacao, 'lotacao'),
                'rearquivador_id' => null,
                'destino_id' => $lotacao->id,
            ];
        });
    }

    /**
     * Gera uma solicitação no status devolvida.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function devolvida()
    {
        return $this->state(function () {
            $lotacao = Lotacao::factory()->create();

            return [
                'solicitante_id' => Usuario::factory()->for($lotacao, 'lotacao'),
                'recebedor_id' => Usuario::factory()->for($lotacao, 'lotacao'),
                'destino_id' => $lotacao->id,
            ];
        });
    }
}
