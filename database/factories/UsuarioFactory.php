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
 * @see https://laravel.com/docs/database-testing
 * @see https://fakerphp.github.io/
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * {@inheritdoc}
     */
    public function definition()
    {
        return array_merge(
            (new UsuarioCorporativoFactory())->definition(),
            [
                'nome' => $this->faker->optional()->text(50),
                'username' => $this->faker->unique()->text(20),
                'password' => null,
                'guid' => $this->faker->unique()->uuid(),
                'domain' => $this->faker->domainName(),
                'perfil_id' => Perfil::factory(),
                'lotacao_id' => Lotacao::SEM_LOTACAO,
                'perfil_concedido_por' => null,
                'antigo_perfil_id' => null,
            ]
        );
    }
}
