<?php

namespace Database\Factories;

use App\Models\Estante;
use App\Models\Prateleira;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prateleira>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class PrateleiraFactory extends Factory
{
    protected $model = Prateleira::class;

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
            'estante_id' => Estante::factory(),
        ];
    }
}
