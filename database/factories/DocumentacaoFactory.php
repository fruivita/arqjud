<?php

namespace Database\Factories;

use App\Models\Documentacao;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Documentacao>
 *
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class DocumentacaoFactory extends Factory
{
    protected $model = Documentacao::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return [
            'app_link' => $this->faker->unique()->url(),
            'doc_link' => $this->faker->url(),
        ];
    }
}
