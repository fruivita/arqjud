<?php

namespace Database\Factories;

use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use FruiVita\Corporativo\Database\Factories\UsuarioFactory as UsuarioCorporativoFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Usuario>
 *
 * @see https://laravel.com/docs/9.x/database-testing
 * @see https://fakerphp.github.io/
 */
class UsuarioFactory extends Factory
{
    /**
     * {@inheritdoc}
     */
    protected $model = Usuario::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return array_merge(
            (new UsuarioCorporativoFactory())->definition(),
            [
                'username' => fake()->unique()->text(20),
                'password' => null,
                'guid' => fake()->unique()->uuid(),
                'domain' => fake()->domainName(),
                'perfil_id' => Perfil::factory(),
                'lotacao_id' => Lotacao::factory(),
                'perfil_concedido_por' => null,
                'antigo_perfil_id' => null,
            ]
        );
    }
}
