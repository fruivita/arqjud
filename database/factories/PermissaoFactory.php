<?php

namespace Database\Factories;

use App\Models\Permissao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permissao>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class PermissaoFactory extends Factory
{
    protected $model = Permissao::class;

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
