<?php

namespace Database\Factories;

use App\Models\Caixa;
use App\Models\VolumeCaixa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VolumeCaixa>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class VolumeCaixaFactory extends Factory
{
    protected $model = VolumeCaixa::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $numero = $this->faker->unique()->numberBetween(1, 65535);

        return [
            'numero' => $numero,
            'apelido' => "Vol. {$numero}",
            'caixa_id' => Caixa::factory(),
        ];
    }
}
