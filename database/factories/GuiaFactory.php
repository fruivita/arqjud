<?php

namespace Database\Factories;

use App\Models\Guia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guia>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class GuiaFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Guia::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => fake()->unique()->numberBetween(1, 99999),
            'ano' => fake()->numberBetween(1900, 2020),
            'remetente' => [
                'username' => fake()->firstName(),
                'nome' => fake()->name(),
            ],
            'recebedor' => [
                'username' => fake()->firstName(),
                'nome' => fake()->name(),
            ],
            'lotacao_destinataria' => [
                'sigla' => fake()->word(),
                'nome' => fake()->company(),
            ],
            'processos' => $this->processos(),
            'gerada_em' => now()->subWeeks(rand(1, 30)),
            'descricao' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Gera uma solicitação no status de solicitada.
     *
     * @return array
     */
    private function processos()
    {
        $processos = [];

        foreach (range(1, rand(1, 10)) as $value) {
            $processos[] = [
                'numero' => fake()->numeroProcessoCNJ(),
                'qtd_volumes' => fake()->numberBetween(1, 20),
            ];
        }

        return $processos;
    }
}
