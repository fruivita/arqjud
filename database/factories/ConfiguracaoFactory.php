<?php

namespace Database\Factories;

use App\Models\Configuracao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Configuracao>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class ConfiguracaoFactory extends Factory
{
    protected $model = Configuracao::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, 255),
            'superadmin' => $this->faker->unique()->text(20),
        ];
    }
}
