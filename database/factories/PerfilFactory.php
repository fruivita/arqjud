<?php

namespace Database\Factories;

use App\Models\Perfil;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Perfil>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class PerfilFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Perfil::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $nome = fake()->unique()->text(50);

        return [
            'nome' => $nome,
            'slug' => str($nome)->slug()->toString(),
            'poder' => fake()->unique()->numberBetween(1001, 9998),
            'descricao' => fake()->optional()->sentence(),
        ];
    }
}
