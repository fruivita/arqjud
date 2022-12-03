<?php

namespace Database\Factories;

use App\Models\Estante;
use App\Models\Prateleira;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Prateleira>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class PrateleiraFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Prateleira::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'numero' => fake()->unique()->numerify('#########'),
            'descricao' => fake()->optional()->sentence(),
            'estante_id' => Estante::factory(),
        ];
    }
}
