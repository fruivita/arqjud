<?php

namespace Database\Factories;

use App\Models\Andar;
use App\Models\Predio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Andar>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class AndarFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Andar::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $numero = fake()->unique()->numberBetween(-100, 300);

        return [
            'numero' => $numero,
            'apelido' => "{$numero}º",
            'descricao' => fake()->optional()->sentence(),
            'predio_id' => Predio::factory(),
        ];
    }
}
