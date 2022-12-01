<?php

namespace Database\Factories;

use App\Models\Localidade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Localidade>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class LocalidadeFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Localidade::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'nome' => fake()->unique()->text(40),
            'descricao' => fake()->optional()->sentence(),
        ];
    }
}
