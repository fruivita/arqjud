<?php

namespace Database\Factories;

use App\Models\TipoProcesso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TipoProcesso>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class TipoProcessoFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = TipoProcesso::class;

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
