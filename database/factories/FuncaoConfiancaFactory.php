<?php

namespace Database\Factories;

use App\Models\FuncaoConfianca;
use FruiVita\Corporativo\Database\Factories\FuncaoConfiancaFactory as FuncaoConfiancaCorporativoFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FuncaoConfianca>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class FuncaoConfiancaFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = FuncaoConfianca::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return (new FuncaoConfiancaCorporativoFactory())->definition();
    }
}
