<?php

namespace Database\Factories;

use App\Models\Caixa;
use App\Models\VolumeCaixa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VolumeCaixa>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class VolumeCaixaFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = VolumeCaixa::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'caixa_id' => Caixa::factory(),
            'numero' => fake()->unique()->numberBetween(1, 65535),
            'descricao' => fake()->optional()->sentence(),
        ];
    }
}
