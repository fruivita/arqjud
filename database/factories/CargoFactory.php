<?php

namespace Database\Factories;

use App\Models\Cargo;
use FruiVita\Corporativo\Database\Factories\CargoFactory as CargoCorporativoFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cargo>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class CargoFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Cargo::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return (new CargoCorporativoFactory())->definition();
    }
}
