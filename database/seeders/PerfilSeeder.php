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
                ->map(function ($item) use ($now) {
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
                'poder' => 8999,
                'descricao' => 'Perfil com acesso a todas as funções de negócio da aplicação. Não possui acesso às funções de administração.',
            ],
            [
                'nome' => 'Operador',
                'slug' => Perfil::OPERADOR,
                'poder' => 8500,
                'descricao' => 'Perfil com acesso a funções de negócio não críticas.',
            ],
            [
                'nome' => 'Observador',
                'slug' => Perfil::OBSERVADOR,
                'poder' => 7999,
                'descricao' => 'Perfil com acesso de visualização em todas as funções de negócio da aplicação. Não possui acesso as funções de administração.',
            ],
            [
                'nome' => 'Padrão',
                'slug' => Perfil::PADRAO,
                'poder' => 1999,
                'descricao' => 'Perfil com acesso apenas às funções mínimas.',
            ],
        ]);
    }
}
