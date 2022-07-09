<?php

namespace Database\Factories;

use App\Models\Andar;
use App\Models\Predio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Andar>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class AndarFactory extends Factory
{
    protected $model = Andar::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $numero = $this->faker->unique()->numberBetween(-100000, 100000);

        return [
            'numero' => $numero,
            'apelido' => "{$numero}º",
            'descricao' => $this->faker->optional()->sentence(),
            'predio_id' => Predio::factory(),
        ];
    }
}
