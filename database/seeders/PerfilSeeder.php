<?php

namespace Database\Seeders;

use App\Models\Perfil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

/**
 * @see https://laravel.com/docs/9.x/seeding
 */
class PerfilSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('perfis')->insert(
            $this->todosPerfis()
                ->map(function (array $item) use ($now) {
                    $item['created_at'] = $now;
                    $item['updated_at'] = $now;

                    return $item;
                })
                ->toArray()
        );
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function todosPerfis()
    {
        return LazyCollection::make([
            [
                'nome' => 'Administrador',
                'slug' => Perfil::ADMINISTRADOR,
                'poder' => 9999,
                'descricao' => 'Perfil com acesso a todas as operações da aplicação.',
            ],
            [
                'nome' => 'Gerente de negócio',
                'slug' => Perfil::GERENTE_NEGOCIO,
                'poder' => 9900,
                'descricao' => 'Perfil com acesso a todas as funções de negócio da aplicação.',
            ],
            [
                'nome' => 'Operador',
                'slug' => Perfil::OPERADOR,
                'poder' => 9800,
                'descricao' => 'Perfil com acesso a funções de negócio não críticas.',
            ],
            [
                'nome' => 'Observador',
                'slug' => Perfil::OBSERVADOR,
                'poder' => 9700,
                'descricao' => 'Perfil com acesso de visualização em todas as funções de negócio da aplicação.',
            ],
            [
                'nome' => 'Padrão',
                'slug' => Perfil::PADRAO,
                'poder' => 1000,
                'descricao' => 'Perfil com acesso apenas às funções mínimas.',
            ],
        ]);
    }
}
