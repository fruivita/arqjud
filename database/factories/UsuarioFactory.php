<?php

namespace Database\Factories;

use App\Models\Cargo;
use App\Models\FuncaoConfianca;
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
                'matricula' => fake()->unique()->bothify('??#####'),
                'nome' => fake()->text(50),
                'password' => null,
                'ultimo_login' => now()->subWeeks(rand(1, 30)),
                'ip' => fake()->ipv4(),
                'guid' => fake()->unique()->uuid(),
                'domain' => fake()->domainName(),
                'perfil_id' => Perfil::factory(),
                'lotacao_id' => Lotacao::factory(),
            ]
        );
    }

    /**
     * Gera um usuário com cargo.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function comCargo()
    {
        return $this->state(function () {
            return [
                'cargo_id' => Cargo::factory(),
            ];
        });
    }

    /**
     * Gera um usuário com todos os atributos opções definidos.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completo()
    {
        return $this->state(function () {
            return [
                'cargo_id' => Cargo::factory(),
                'lotacao_id' => Lotacao::factory(),
                'funcao_confianca_id' => FuncaoConfianca::factory(),
            ];
        });
    }
}
