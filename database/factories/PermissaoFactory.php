<?php

namespace Database\Factories;

use App\Models\Permissao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permissao>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class PermissaoFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Permissao::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        $nome = fake()->unique()->text(50);

        return [
            'nome' => $nome,
            'slug' => str($nome)->slug()->toString(),
            'descricao' => fake()->optional()->sentence(),
        ];
    }
}
