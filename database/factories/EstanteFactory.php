<?php

namespace Database\Factories;

use App\Models\Sala;
use App\Models\Estante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estante>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class EstanteFactory extends Factory
{
    protected $model = Estante::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $numero = $this->faker->unique()->numberBetween();

        return [
            'numero' => $numero,
            'apelido' => $numero,
            'descricao' => $this->faker->optional()->sentence(),
            'sala_id' => Sala::factory(),
        ];
    }
}
