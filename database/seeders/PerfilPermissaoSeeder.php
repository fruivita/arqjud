<?php

namespace Database\Seeders;

use App\Enums\Permissao;
use App\Models\Perfil;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

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
            ->map(function ($item) use ($agora) {
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
     * @return \Illuminate\Support\LazyCollection
     */
    private function todosPerfisPermissoes()
    {
        return $this->permissoesPerfilAdministrador()
        ->concat($this->permissoesPerfilGerenteNegocio())
        ->concat($this->permissoesPerfilObservador())
        ->concat($this->permissoesPerfilPadrao());
    }

    /**
     * Permissões iniciais do perfil administrador.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPerfilAdministrador()
    {
        return LazyCollection::make([
            Permissao::LocalidadeViewAny->value,
            Permissao::LocalidadeView->value,
            Permissao::LocalidadeCreate->value,
            Permissao::LocalidadeUpdate->value,
            Permissao::LocalidadeDelete->value,
            Permissao::PredioViewAny->value,
            Permissao::PredioView->value,
            Permissao::PredioCreate->value,
            Permissao::PredioUpdate->value,
            Permissao::PredioDelete->value,
            Permissao::AndarViewAny->value,
            Permissao::AndarView->value,
            Permissao::AndarCreate->value,
            Permissao::AndarUpdate->value,
            Permissao::AndarDelete->value,
            Permissao::SalaViewAny->value,
            Permissao::SalaView->value,
            Permissao::SalaCreate->value,
            Permissao::SalaUpdate->value,
            Permissao::SalaDelete->value,
            Permissao::EstanteViewAny->value,
            Permissao::EstanteView->value,
            Permissao::EstanteCreate->value,
            Permissao::EstanteUpdate->value,
            Permissao::EstanteDelete->value,
            Permissao::PrateleiraViewAny->value,
            Permissao::PrateleiraView->value,
            Permissao::PrateleiraCreate->value,
            Permissao::PrateleiraUpdate->value,
            Permissao::PrateleiraDelete->value,
            Permissao::CaixaViewAny->value,
            Permissao::CaixaView->value,
            Permissao::CaixaCreate->value,
            Permissao::CaixaCreateMany->value,
            Permissao::CaixaUpdate->value,
            Permissao::CaixaDelete->value,
            Permissao::VolumeCaixaViewAny->value,
            Permissao::VolumeCaixaView->value,
            Permissao::VolumeCaixaCreate->value,
            Permissao::VolumeCaixaUpdate->value,
            Permissao::VolumeCaixaDelete->value,

            Permissao::ConfiguracaoView->value,
            Permissao::ConfiguracaoUpdate->value,
            Permissao::DelegacaoViewAny->value,
            Permissao::DelegacaoCreate->value,
            Permissao::DocumentacaoViewAny->value,
            Permissao::DocumentacaoView->value,
            Permissao::DocumentacaoCreate->value,
            Permissao::DocumentacaoUpdate->value,
            Permissao::DocumentacaoDelete->value,
            Permissao::ImportacaoCreate->value,
            Permissao::LogViewAny->value,
            Permissao::LogDelete->value,
            Permissao::LogDownload->value,
            Permissao::PermissaoViewAny->value,
            Permissao::PermissaoView->value,
            Permissao::PermissaoUpdate->value,
            Permissao::PerfilViewAny->value,
            Permissao::PerfilView->value,
            Permissao::PerfilUpdate->value,
            Permissao::SimulacaoCreate->value,
            Permissao::UsuarioViewAny->value,
            Permissao::UsuarioUpdate->value,
        ])->map(function ($id_permissao) {
            $perfil_permissao['perfil_id'] = Perfil::ADMINISTRADOR;
            $perfil_permissao['permissao_id'] = $id_permissao;

            return $perfil_permissao;
        });
    }

    /**
     * Permissões inciais do perfil gerente de negocio.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPerfilGerenteNegocio()
    {
        return LazyCollection::make([
            // ...
        ])->map(function ($id_permissao) {
            $perfil_permissao['perfil_id'] = Perfil::GERENTE_NEGOCIO;
            $perfil_permissao['permissao_id'] = $id_permissao;

            return $perfil_permissao;
        });
    }

    /**
     * Permissoes iniciais do perfil observador.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPerfilObservador()
    {
        return LazyCollection::make([
            // ...
        ])->map(function ($id_permissao) {
            $perfil_permissao['perfil_id'] = Perfil::OBSERVADOR;
            $perfil_permissao['permissao_id'] = $id_permissao;

            return $perfil_permissao;
        });
    }

    /**
     * Permissoes iniciais do perfil padrão.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPerfilPadrao()
    {
        return LazyCollection::make([
            // ...
        ])->map(function ($id_permissao) {
            $perfil_permissao['perfil_id'] = Perfil::PADRAO;
            $perfil_permissao['permissao_id'] = $id_permissao;

            return $perfil_permissao;
        });
    }
}
