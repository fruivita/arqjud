<?php

namespace Database\Factories;

use App\Models\Perfil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perfil>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class PerfilFactory extends Factory
{
    protected $model = Perfil::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, maxIntegerSeguro()),
            'nome' => $this->faker->unique()->text(20),
            'descricao' => $this->faker->optional()->sentence(),
        ];
    }
}
