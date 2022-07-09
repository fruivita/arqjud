<?php

namespace Database\Seeders;

use App\Enums\Permissao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

/**
 * @see https://laravel.com/docs/9.x/seeding
 */
class PermissaoSeeder extends Seeder
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

        DB::table('permissoes')->insert(
            $this->todasPermissoes()
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
    private function todasPermissoes()
    {
        return $this->permissoesConfiguracao()
        ->concat($this->permissoesDelegacao())
        ->concat($this->permissoesDocumentacao())
        ->concat($this->permissoesImportacao())
        ->concat($this->permissoesLog())
        ->concat($this->permissoesPermissao())
        ->concat($this->permissoesPerfil())
        ->concat($this->permissoesSimulacao())
        ->concat($this->permissoesLocalidade())
        ->concat($this->permissoesPredio())
        ->concat($this->permissoesAndar())
        ->concat($this->permissoesSala())
        ->concat($this->permissoesEstante())
        ->concat($this->permissoesPrateleira())
        ->concat($this->permissoesCaixa())
        ->concat($this->permissoesVolumeCaixa())
        ->concat($this->permissoesUsuario());
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesCaixa()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::CaixaViewAny->value,
                'nome' => __('Caixa: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as caixas cadastradas.'),
            ],
            [
                'id' => Permissao::CaixaView->value,
                'nome' => __('Caixa: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente as caixas cadastradas.'),
            ],
            [
                'id' => Permissao::CaixaCreate->value,
                'nome' => __('Caixa: Criar uma'),
                'descricao' => __('Permissão para criar individualmente as caixas.'),
            ],
            [
                'id' => Permissao::CaixaCreateMany->value,
                'nome' => __('Caixa: Criar muitas'),
                'descricao' => __('Permissão para criar múltiplas caixas de uma vez.'),
            ],
            [
                'id' => Permissao::CaixaUpdate->value,
                'nome' => __('Caixa: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente as caixas cadastradas.'),
            ],
            [
                'id' => Permissao::CaixaDelete->value,
                'nome' => __('Caixa: Excluir uma'),
                'descricao' => __('Permissão para excluir individualmente as caixas cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesVolumeCaixa()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::VolumeCaixaViewAny->value,
                'nome' => __('Volume da caixa: Visualizar todos'),
                'descricao' => __('Permissão para visualizar todos os volumes cadastrados da caixa.'),
            ],
            [
                'id' => Permissao::VolumeCaixaView->value,
                'nome' => __('Volume da caixa: Visualizar um'),
                'descricao' => __('Permissão para visualizar individualmente os volumes cadastrados da caixa.'),
            ],
            [
                'id' => Permissao::VolumeCaixaCreate->value,
                'nome' => __('Volume da caixa: Criar'),
                'descricao' => __('Permissão para criar volumes da caixa.'),
            ],
            [
                'id' => Permissao::VolumeCaixaUpdate->value,
                'nome' => __('Volume da caixa: Atualizar um'),
                'descricao' => __('Permissão para atualizar individualmente os volumes cadastrados da caixa.'),
            ],
            [
                'id' => Permissao::VolumeCaixaDelete->value,
                'nome' => __('Volume da caixa: Excluir um'),
                'descricao' => __('Permissão para excluir individualmente os volumes cadastrados da caixa.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPredio()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::PredioViewAny->value,
                'nome' => __('Prédio: Visualizar todos'),
                'descricao' => __('Permissão para visualizar todos os prédios cadastrados.'),
            ],
            [
                'id' => Permissao::PredioView->value,
                'nome' => __('Prédio: Visualizar um'),
                'descricao' => __('Permissão para visualizar individualmente os prédios cadastrados.'),
            ],
            [
                'id' => Permissao::PredioCreate->value,
                'nome' => __('Prédio: Criar um'),
                'descricao' => __('Permissão para criar individualmente os prédios.'),
            ],
            [
                'id' => Permissao::PredioUpdate->value,
                'nome' => __('Prédio: Atualizar um'),
                'descricao' => __('Permissão para atualizar individualmente os prédios cadastrados.'),
            ],
            [
                'id' => Permissao::PredioDelete->value,
                'nome' => __('Prédio: Excluir um'),
                'descricao' => __('Permissão para excluir individualmente os prédios cadastrados.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesConfiguracao()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::ConfiguracaoView->value,
                'nome' => __('Configuração da aplicação: Visualizar'),
                'descricao' => __('Permissão para visualizar as configurações da aplicação cadastradas.'),
            ],
            [
                'id' => Permissao::ConfiguracaoUpdate->value,
                'nome' => __('Configuração da aplicação: Atualizar'),
                'descricao' => __('Permissão para atualizar as configurações da aplicação cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesDocumentacao()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::DocumentacaoViewAny->value,
                'nome' => __('Documentação: Visualizar todas'),
                'descricao' => __('Permissão para visualizar toda adocumentação da aplicação cadastrada.'),
            ],
            [
                'id' => Permissao::DocumentacaoView->value,
                'nome' => __('Documentação: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente odocumentação da aplicação cadastrada.'),
            ],
            [
                'id' => Permissao::DocumentacaoCreate->value,
                'nome' => __('Documentação: Criar uma'),
                'descricao' => __('Permissão para criar individualmente adocumentação da aplicação.'),
            ],
            [
                'id' => Permissao::DocumentacaoUpdate->value,
                'nome' => __('Documentação: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente adocumentação da aplicação cadastrada.'),
            ],
            [
                'id' => Permissao::DocumentacaoDelete->value,
                'nome' => __('Documentação: Excluir uma'),
                'descricao' => __('Permissão para excluir individualmente adocumentação da aplicação cadastrada.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesDelegacao()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::DelegacaoViewAny->value,
                'nome' => __('Delegação: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as delegações da lotação.'),
            ],
            [
                'id' => Permissao::DelegacaoCreate->value,
                'nome' => __('Delegação: Criar'),
                'descricao' => __('Permissão para delegar o perfil (e suas permissões) para outro usuário de mesma lotação.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesAndar()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::AndarViewAny->value,
                'nome' => __('Andar: Visualizar todos'),
                'descricao' => __('Permissão para visualizar todos os andares cadastrados.'),
            ],
            [
                'id' => Permissao::AndarView->value,
                'nome' => __('Andar: Visualizar um'),
                'descricao' => __('Permissão para visualizar individualmente os andares cadastrados.'),
            ],
            [
                'id' => Permissao::AndarCreate->value,
                'nome' => __('Andar: Criar um'),
                'descricao' => __('Permissão para criar individualmente os andares.'),
            ],
            [
                'id' => Permissao::AndarUpdate->value,
                'nome' => __('Andar: Atualizar um'),
                'descricao' => __('Permissão para atualizar individualmente os andares cadastrados.'),
            ],
            [
                'id' => Permissao::AndarDelete->value,
                'nome' => __('Andar: Excluir um'),
                'descricao' => __('Permissão para excluir individualmente os andares cadastrados.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesImportacao()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::ImportacaoCreate->value,
                'nome' => __('Importação: Criar'),
                'descricao' => __('Permissão para solicitar a importação forçada de dados.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesLog()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::LogViewAny->value,
                'nome' => __('Log: Visualizar todos'),
                'descricao' => __('Permissão para visualizar todos os arquivos de log da aplicação.'),
            ],
            [
                'id' => Permissao::LogDelete->value,
                'nome' => __('Log: Excluir um'),
                'descricao' => __('Permissão para excluir individualmente os arquivos de log da aplicação.'),
            ],
            [
                'id' => Permissao::LogDownload->value,
                'nome' => __('Log: Download de um'),
                'descricao' => __('Permissão para fazer o download individualmente dos arquivos de log da aplicação.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPermissao()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::PermissaoViewAny->value,
                'nome' => __('Permissão: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as permissões cadastradas.'),
            ],
            [
                'id' => Permissao::PermissaoView->value,
                'nome' => __('Permissão: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente as permissões cadastradas.'),
            ],
            [
                'id' => Permissao::PermissaoUpdate->value,
                'nome' => __('Permissão: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente as permissões cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPerfil()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::PerfilViewAny->value,
                'nome' => __('Perfil: Visualizar todos'),
                'descricao' => __('Permissão para visualizar todos os perfis cadastrados.'),
            ],
            [
                'id' => Permissao::PerfilView->value,
                'nome' => __('Perfil: Visualizar um'),
                'descricao' => __('Permissão para visualizar individualmente os perfis cadastrados.'),
            ],
            [
                'id' => Permissao::PerfilUpdate->value,
                'nome' => __('Perfil: Atualizar um'),
                'descricao' => __('Permissão para atualizar individualmente os perfis cadastrados.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesSala()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::SalaViewAny->value,
                'nome' => __('Sala: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as salas cadastradas.'),
            ],
            [
                'id' => Permissao::SalaView->value,
                'nome' => __('Sala: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente as salas cadastradas.'),
            ],
            [
                'id' => Permissao::SalaCreate->value,
                'nome' => __('Sala: Criar uma'),
                'descricao' => __('Permissão para criar individualmente as salas.'),
            ],
            [
                'id' => Permissao::SalaUpdate->value,
                'nome' => __('Sala: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente as salas cadastradas.'),
            ],
            [
                'id' => Permissao::SalaDelete->value,
                'nome' => __('Sala: Excluir uma'),
                'descricao' => __('Permissão para excluir individualmente as salas cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesSimulacao()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::SimulacaoCreate->value,
                'nome' => __('Simulação: Criar'),
                'descricao' => __('Permissão para simular o uso da aplicação como se fosse outro usuário.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesPrateleira()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::PrateleiraViewAny->value,
                'nome' => __('Prateleira: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as prateleiras cadastradas.'),
            ],
            [
                'id' => Permissao::PrateleiraView->value,
                'nome' => __('Prateleira: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente as prateleiras cadastradas.'),
            ],
            [
                'id' => Permissao::PrateleiraCreate->value,
                'nome' => __('Prateleira: Criar uma'),
                'descricao' => __('Permissão para criar individualmente as prateleiras.'),
            ],
            [
                'id' => Permissao::PrateleiraUpdate->value,
                'nome' => __('Prateleira: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente as prateleiras cadastradas.'),
            ],
            [
                'id' => Permissao::PrateleiraDelete->value,
                'nome' => __('Prateleira: Excluir uma'),
                'descricao' => __('Permissão para excluir individualmente as prateleiras cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesLocalidade()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::LocalidadeViewAny->value,
                'nome' => __('Localidade: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as localidades cadastradas.'),
            ],
            [
                'id' => Permissao::LocalidadeView->value,
                'nome' => __('Localidade: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente as localidades cadastradas.'),
            ],
            [
                'id' => Permissao::LocalidadeCreate->value,
                'nome' => __('Localidade: Criar uma'),
                'descricao' => __('Permissão para criar individualmente as localidades.'),
            ],
            [
                'id' => Permissao::LocalidadeUpdate->value,
                'nome' => __('Localidade: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente as localidades cadastradas.'),
            ],
            [
                'id' => Permissao::LocalidadeDelete->value,
                'nome' => __('Localidade: Excluir uma'),
                'descricao' => __('Permissão para excluir individualmente as localidades cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesEstante()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::EstanteViewAny->value,
                'nome' => __('Estante: Visualizar todas'),
                'descricao' => __('Permissão para visualizar todas as estantes cadastradas.'),
            ],
            [
                'id' => Permissao::EstanteView->value,
                'nome' => __('Estante: Visualizar uma'),
                'descricao' => __('Permissão para visualizar individualmente as estantes cadastradas.'),
            ],
            [
                'id' => Permissao::EstanteCreate->value,
                'nome' => __('Estante: Criar uma'),
                'descricao' => __('Permissão para criar individualmente as estantes.'),
            ],
            [
                'id' => Permissao::EstanteUpdate->value,
                'nome' => __('Estante: Atualizar uma'),
                'descricao' => __('Permissão para atualizar individualmente as estantes cadastradas.'),
            ],
            [
                'id' => Permissao::EstanteDelete->value,
                'nome' => __('Estante: Excluir uma'),
                'descricao' => __('Permissão para excluir individualmente as estantes cadastradas.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesUsuario()
    {
        return LazyCollection::make([
            [
                'id' => Permissao::UsuarioViewAny->value,
                'nome' => __('Usuário: Visualizar todos'),
                'descricao' => __('Permissão para visualizar todos os usuários cadastrados.'),
            ],
            [
                'id' => Permissao::UsuarioUpdate->value,
                'nome' => __('Usuário: Atualizar um'),
                'descricao' => __('Permissão para atualizar individualmente os usuários cadastrados.'),
            ],
        ]);
    }
}
