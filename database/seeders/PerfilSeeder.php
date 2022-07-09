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
        $agora = now()->format('Y-m-d H:i:s');

        DB::table('perfis')->insert(
            $this->todosPerfis()
            ->map(function ($item) use ($agora) {
                $item['created_at'] = $agora;
                $item['updated_at'] = $agora;

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
                'id' => Perfil::ADMINISTRADOR,
                'nome' => __('Administrador'),
                'descricao' => __('Perfil com acesso a todas as operações da aplicação.'),
            ],
            [
                'id' => Perfil::GERENTE_NEGOCIO,
                'nome' => __('Gerente de negócio'),
                'descricao' => __('Perfil com acesso a todas as funções de negócio da aplicação. Não possui acesso às funções de administração.'),
            ],
            [
                'id' => Perfil::OBSERVADOR,
                'nome' => __('Observador'),
                'descricao' => __('Perfil com acesso de visualização em todas as funções de negócio da aplicação. Não possui acesso para visualização as funções de administração.'),
            ],
            [
                'id' => Perfil::PADRAO,
                'nome' => __('Padrão'),
                'descricao' => __('Perfil com acesso apenas às funções mínimas.'),
            ],
        ]);
    }
}
