<?php

namespace Database\Seeders;

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * @see https://laravel.com/docs/9.x/seeding
 */
class PerfilPermissaoSeeder extends Seeder
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

        DB::table('perfil_permissao')->insert(
            $this
                ->todosPerfisPermissoes()
                ->map(function (array $item) use ($agora) {
                    $item['created_at'] = $agora;
                    $item['updated_at'] = $agora;

                    return $item;
                })
                ->toArray()
        );
    }

    /**
     * Todos os perfis e suas respectivas permissões.
     *
     * @return \Illuminate\Support\Collection
     */
    private function todosPerfisPermissoes()
    {
        $perfis = Perfil::whereIn('slug', [
            Perfil::ADMINISTRADOR,
            Perfil::GERENTE_NEGOCIO,
            Perfil::OPERADOR,
            Perfil::OBSERVADOR,
            Perfil::PADRAO,
        ])->pluck('id', 'slug');

        return $this->permissoesPerfilAdministrador($perfis->get(Perfil::ADMINISTRADOR))
            ->concat($this->permissoesPerfilGerenteNegocio($perfis->get(Perfil::GERENTE_NEGOCIO)))
            ->concat($this->permissoesPerfilOperador($perfis->get(Perfil::OPERADOR)))
            ->concat($this->permissoesPerfilObservador($perfis->get(Perfil::OBSERVADOR)))
            ->concat($this->permissoesPerfilPadrao($perfis->get(Perfil::PADRAO)));
    }

    /**
     * Permissões iniciais do perfil administrador.
     *
     * @return \Illuminate\Support\Collection
     */
    private function permissoesPerfilAdministrador(int $id_perfil)
    {
        return Permissao::whereIn('slug', [
            Permissao::IMPORTACAO_CREATE,
            Permissao::LOG_VIEW_ANY,
            Permissao::LOG_VIEW,
            Permissao::LOG_DELETE,
            Permissao::ATIVIDADE_VIEW_ANY,
            Permissao::ATIVIDADE_VIEW,
            Permissao::ATIVIDADE_DELETE,
            Permissao::PERMISSAO_VIEW_ANY,
            Permissao::PERMISSAO_VIEW,
            Permissao::PERMISSAO_UPDATE,
            Permissao::PERFIL_VIEW_ANY,
            Permissao::PERFIL_VIEW,
            Permissao::PERFIL_CREATE,
            Permissao::PERFIL_UPDATE,
            Permissao::PERFIL_DELETE,
            Permissao::USUARIO_VIEW_ANY,
            Permissao::USUARIO_VIEW,
            Permissao::USUARIO_UPDATE,
            Permissao::LOTACAO_VIEW_ANY,
            Permissao::LOTACAO_UPDATE,
            Permissao::LOCALIDADE_VIEW_ANY,
            Permissao::LOCALIDADE_VIEW,
            Permissao::LOCALIDADE_CREATE,
            Permissao::LOCALIDADE_UPDATE,
            Permissao::LOCALIDADE_DELETE,
            Permissao::PREDIO_VIEW_ANY,
            Permissao::PREDIO_VIEW,
            Permissao::PREDIO_CREATE,
            Permissao::PREDIO_UPDATE,
            Permissao::PREDIO_DELETE,
            Permissao::ANDAR_VIEW_ANY,
            Permissao::ANDAR_VIEW,
            Permissao::ANDAR_CREATE,
            Permissao::ANDAR_UPDATE,
            Permissao::ANDAR_DELETE,
            Permissao::SALA_VIEW_ANY,
            Permissao::SALA_VIEW,
            Permissao::SALA_CREATE,
            Permissao::SALA_UPDATE,
            Permissao::SALA_DELETE,
            Permissao::ESTANTE_VIEW_ANY,
            Permissao::ESTANTE_VIEW,
            Permissao::ESTANTE_CREATE,
            Permissao::ESTANTE_UPDATE,
            Permissao::ESTANTE_DELETE,
            Permissao::PRATELEIRA_VIEW_ANY,
            Permissao::PRATELEIRA_VIEW,
            Permissao::PRATELEIRA_CREATE,
            Permissao::PRATELEIRA_UPDATE,
            Permissao::PRATELEIRA_DELETE,
            Permissao::CAIXA_VIEW_ANY,
            Permissao::CAIXA_VIEW,
            Permissao::CAIXA_CREATE,
            Permissao::CAIXA_UPDATE,
            Permissao::CAIXA_DELETE,
            Permissao::VOLUME_CAIXA_VIEW_ANY,
            Permissao::VOLUME_CAIXA_VIEW,
            Permissao::VOLUME_CAIXA_CREATE,
            Permissao::VOLUME_CAIXA_UPDATE,
            Permissao::VOLUME_CAIXA_DELETE,
            Permissao::PROCESSO_VIEW_ANY,
            Permissao::PROCESSO_VIEW,
            Permissao::PROCESSO_CREATE,
            Permissao::PROCESSO_UPDATE,
            Permissao::PROCESSO_DELETE,
            Permissao::MOVER_PROCESSO_CREATE,
            Permissao::SOLICITACAO_VIEW_ANY,
            Permissao::SOLICITACAO_CREATE,
            Permissao::SOLICITACAO_UPDATE,
            Permissao::SOLICITACAO_DELETE,
            Permissao::SOLICITACAO_EXTERNA_VIEW_ANY,
            Permissao::SOLICITACAO_EXTERNA_CREATE,
            Permissao::SOLICITACAO_EXTERNA_DELETE,
            Permissao::GUIA_VIEW_ANY,
            Permissao::GUIA_VIEW,
        ])
            ->pluck('id')
            ->map(function (int $id_permissao) use ($id_perfil) {
                $perfil_permissao['perfil_id'] = $id_perfil;
                $perfil_permissao['permissao_id'] = $id_permissao;

                return $perfil_permissao;
            });
    }

    /**
     * Permissões inciais do perfil gerente de negocio.
     *
     * @param  int  $id_perfil
     * @return \Illuminate\Support\Collection
     */
    private function permissoesPerfilGerenteNegocio(int $id_perfil)
    {
        $id_perfil = Perfil::gerenteNegocio()->id;

        return Permissao::whereIn('slug', [
            //
        ])
            ->pluck('id')
            ->map(function (int $id_permissao) use ($id_perfil) {
                $perfil_permissao['perfil_id'] = $id_perfil;
                $perfil_permissao['permissao_id'] = $id_permissao;

                return $perfil_permissao;
            });
    }

    /**
     * Permissões inciais do perfil operador.
     *
     * @param  int  $id_perfil
     * @return \Illuminate\Support\Collection
     */
    private function permissoesPerfilOperador(int $id_perfil)
    {
        $id_perfil = Perfil::operador()->id;

        return Permissao::whereIn('slug', [
            //
        ])
            ->pluck('id')
            ->map(function (int $id_permissao) use ($id_perfil) {
                $perfil_permissao['perfil_id'] = $id_perfil;
                $perfil_permissao['permissao_id'] = $id_permissao;

                return $perfil_permissao;
            });
    }

    /**
     * Permissoes iniciais do perfil observador.
     *
     * @param  int  $id_perfil
     * @return \Illuminate\Support\Collection
     */
    private function permissoesPerfilObservador(int $id_perfil)
    {
        $id_perfil = Perfil::observador()->id;

        return Permissao::whereIn('slug', [
            //
        ])
            ->pluck('id')
            ->map(function (int $id_permissao) use ($id_perfil) {
                $perfil_permissao['perfil_id'] = $id_perfil;
                $perfil_permissao['permissao_id'] = $id_permissao;

                return $perfil_permissao;
            });
    }

    /**
     * Permissoes iniciais do perfil padrão.
     *
     * @param  int  $id_perfil
     * @return \Illuminate\Support\Collection
     */
    private function permissoesPerfilPadrao(int $id_perfil)
    {
        $id_perfil = Perfil::padrao()->id;

        return Permissao::whereIn('slug', [
            //
        ])
            ->pluck('id')
            ->map(function (int $id_permissao) use ($id_perfil) {
                $perfil_permissao['perfil_id'] = $id_perfil;
                $perfil_permissao['permissao_id'] = $id_permissao;

                return $perfil_permissao;
            });
    }
}
