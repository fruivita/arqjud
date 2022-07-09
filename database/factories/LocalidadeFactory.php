<?php

namespace Database\Factories;

use App\Models\Localidade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Localidade>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class LocalidadeFactory extends Factory
{
    protected $model = Localidade::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'nome' => $this->faker->unique()->text(20),
            'descricao' => $this->faker->optional()->sentence(),
        ];
    }
}
