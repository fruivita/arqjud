<?php

namespace Database\Seeders;

use App\Models\Permissao;
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
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $agora = now()->format('Y-m-d H:i:s');

        DB::table('permissoes')->insert(
            $this->todasPermissoes()
                ->map(function (array $item) use ($agora) {
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
        return $this->permissoesImportacao()
            ->concat($this->permissoesLog())
            ->concat($this->permissoesAtividade())
            ->concat($this->permissoesPermissao())
            ->concat($this->permissoesPerfil())
            ->concat($this->permissoesUsuario())
            ->concat($this->permissoesLotacao())
            ->concat($this->permissoesLocalidade())
            ->concat($this->permissoesPredio())
            ->concat($this->permissoesAndar())
            ->concat($this->permissoesSala())
            ->concat($this->permissoesEstante())
            ->concat($this->permissoesPrateleira())
            ->concat($this->permissoesCaixa())
            ->concat($this->permissoesProcesso())
            ->concat($this->permissoesMoverProcesso())
            ->concat($this->permissoesSolicitacao())
            ->concat($this->permissoesSolicitacaoExterna())
            ->concat($this->permissoesGuia());
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesImportacao()
    {
        return LazyCollection::make([
            [
                'nome' => 'Importação: Criar',
                'slug' => Permissao::IMPORTACAO_CREATE,
                'descricao' => 'Permissão para solicitar a importação forçada de dados.',
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
                'nome' => 'Log: Visualizar todos',
                'slug' => Permissao::LOG_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os arquivos de log da aplicação.',
            ],
            [
                'nome' => 'Log: Visualizar',
                'slug' => Permissao::LOG_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os arquivos de log da aplicação.',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesAtividade()
    {
        return LazyCollection::make([
            [
                'nome' => 'Atividade: Visualizar todas',
                'slug' => Permissao::ATIVIDADE_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os registros de atividade e/ou uso da aplicação.',
            ],
            [
                'nome' => 'Atividade: Visualizar',
                'slug' => Permissao::ATIVIDADE_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os registros de atividade e/ou uso da aplicação.',
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
                'nome' => 'Permissão: Visualizar todas',
                'slug' => Permissao::PERMISSAO_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as permissões cadastradas.',
            ],
            [
                'nome' => 'Permissão: Visualizar',
                'slug' => Permissao::PERMISSAO_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes as permissões cadastradas.',
            ],
            [
                'nome' => 'Permissão: Atualizar',
                'slug' => Permissao::PERMISSAO_UPDATE,
                'descricao' => 'Permissão para atualizar as permissões cadastradas.',
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
                'nome' => 'Perfil: Visualizar todos',
                'slug' => Permissao::PERFIL_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os perfis cadastrados.',
            ],
            [
                'nome' => 'Perfil: Visualizar',
                'slug' => Permissao::PERFIL_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os perfis cadastrados.',
            ],
            [
                'nome' => 'Perfil: Criar',
                'slug' => Permissao::PERFIL_CREATE,
                'descricao' => 'Permissão para criar os perfis.',
            ],
            [
                'nome' => 'Perfil: Atualizar',
                'slug' => Permissao::PERFIL_UPDATE,
                'descricao' => 'Permissão para atualizar os perfis cadastrados.',
            ],
            [
                'nome' => 'Perfil: Excluir',
                'slug' => Permissao::PERFIL_DELETE,
                'descricao' => 'Permissão para excluir os perfis cadastradas.',
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
                'nome' => 'Usuário: Visualizar todos',
                'slug' => Permissao::USUARIO_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os usuários cadastrados.',
            ],
            [
                'nome' => 'Usuário: Visualizar',
                'slug' => Permissao::USUARIO_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os usuários cadastrados.',
            ],
            [
                'nome' => 'Usuário: Atualizar',
                'slug' => Permissao::USUARIO_UPDATE,
                'descricao' => 'Permissão para atualizar os usuários cadastrados.',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesLotacao()
    {
        return LazyCollection::make([
            [
                'nome' => 'Lotação: Visualizar todas',
                'slug' => Permissao::LOTACAO_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as lotações cadastradas.',
            ],
            [
                'nome' => 'Lotação: Atualizar',
                'slug' => Permissao::LOTACAO_UPDATE,
                'descricao' => 'Permissão para atualizar as lotações cadastradas.',
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
                'nome' => 'Localidade: Visualizar todas',
                'slug' => Permissao::LOCALIDADE_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as localidades cadastradas.',
            ],
            [
                'nome' => 'Localidade: Visualizar',
                'slug' => Permissao::LOCALIDADE_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes as localidades cadastradas.',
            ],
            [
                'nome' => 'Localidade: Criar',
                'slug' => Permissao::LOCALIDADE_CREATE,
                'descricao' => 'Permissão para criar as localidades.',
            ],
            [
                'nome' => 'Localidade: Atualizar',
                'slug' => Permissao::LOCALIDADE_UPDATE,
                'descricao' => 'Permissão para atualizar as localidades cadastradas.',
            ],
            [
                'nome' => 'Localidade: Excluir',
                'slug' => Permissao::LOCALIDADE_DELETE,
                'descricao' => 'Permissão para excluir as localidades cadastradas.',
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
                'nome' => 'Prédio: Visualizar todos',
                'slug' => Permissao::PREDIO_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os prédios cadastrados.',
            ],
            [
                'nome' => 'Prédio: Visualizar',
                'slug' => Permissao::PREDIO_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os prédios cadastrados.',
            ],
            [
                'nome' => 'Prédio: Criar',
                'slug' => Permissao::PREDIO_CREATE,
                'descricao' => 'Permissão para criar os prédios.',
            ],
            [
                'nome' => 'Prédio: Atualizar',
                'slug' => Permissao::PREDIO_UPDATE,
                'descricao' => 'Permissão para atualizar os prédios cadastrados.',
            ],
            [
                'nome' => 'Prédio: Excluir',
                'slug' => Permissao::PREDIO_DELETE,
                'descricao' => 'Permissão para excluir os prédios cadastrados.',
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
                'nome' => 'Andar: Visualizar todos',
                'slug' => Permissao::ANDAR_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os andares cadastrados.',
            ],
            [
                'nome' => 'Andar: Visualizar',
                'slug' => Permissao::ANDAR_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os andares cadastrados.',
            ],
            [
                'nome' => 'Andar: Criar',
                'slug' => Permissao::ANDAR_CREATE,
                'descricao' => 'Permissão para criar os andares.',
            ],
            [
                'nome' => 'Andar: Atualizar',
                'slug' => Permissao::ANDAR_UPDATE,
                'descricao' => 'Permissão para atualizar os andares cadastrados.',
            ],
            [
                'nome' => 'Andar: Excluir',
                'slug' => Permissao::ANDAR_DELETE,
                'descricao' => 'Permissão para excluir os andares cadastrados.',
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
                'nome' => 'Sala: Visualizar todas',
                'slug' => Permissao::SALA_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as salas cadastradas.',
            ],
            [
                'nome' => 'Sala: Visualizar',
                'slug' => Permissao::SALA_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes as salas cadastradas.',
            ],
            [
                'nome' => 'Sala: Criar',
                'slug' => Permissao::SALA_CREATE,
                'descricao' => 'Permissão para criar as salas.',
            ],
            [
                'nome' => 'Sala: Atualizar',
                'slug' => Permissao::SALA_UPDATE,
                'descricao' => 'Permissão para atualizar as salas cadastradas.',
            ],
            [
                'nome' => 'Sala: Excluir',
                'slug' => Permissao::SALA_DELETE,
                'descricao' => 'Permissão para excluir as salas cadastradas.',
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
                'nome' => 'Estante: Visualizar todas',
                'slug' => Permissao::ESTANTE_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as estantes cadastradas.',
            ],
            [
                'nome' => 'Estante: Visualizar',
                'slug' => Permissao::ESTANTE_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes as estantes cadastradas.',
            ],
            [
                'nome' => 'Estante: Criar',
                'slug' => Permissao::ESTANTE_CREATE,
                'descricao' => 'Permissão para criar as estantes.',
            ],
            [
                'nome' => 'Estante: Atualizar',
                'slug' => Permissao::ESTANTE_UPDATE,
                'descricao' => 'Permissão para atualizar as estantes cadastradas.',
            ],
            [
                'nome' => 'Estante: Excluir',
                'slug' => Permissao::ESTANTE_DELETE,
                'descricao' => 'Permissão para excluir as estantes cadastradas.',
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
                'nome' => 'Prateleira: Visualizar todas',
                'slug' => Permissao::PRATELEIRA_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as prateleiras cadastradas.',
            ],
            [
                'nome' => 'Prateleira: Visualizar',
                'slug' => Permissao::PRATELEIRA_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes as prateleiras cadastradas.',
            ],
            [
                'nome' => 'Prateleira: Criar',
                'slug' => Permissao::PRATELEIRA_CREATE,
                'descricao' => 'Permissão para criar as prateleiras.',
            ],
            [
                'nome' => 'Prateleira: Atualizar',
                'slug' => Permissao::PRATELEIRA_UPDATE,
                'descricao' => 'Permissão para atualizar as prateleiras cadastradas.',
            ],
            [
                'nome' => 'Prateleira: Excluir',
                'slug' => Permissao::PRATELEIRA_DELETE,
                'descricao' => 'Permissão para excluir as prateleiras cadastradas.',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesCaixa()
    {
        return LazyCollection::make([
            [
                'nome' => 'Caixa: Visualizar todas',
                'slug' => Permissao::CAIXA_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as caixas cadastradas.',
            ],
            [
                'nome' => 'Caixa: Visualizar',
                'slug' => Permissao::CAIXA_VIEW,
                'descricao' => 'Permissão para visualizar as caixas cadastradas.',
            ],
            [
                'nome' => 'Caixa: Criar',
                'slug' => Permissao::CAIXA_CREATE,
                'descricao' => 'Permissão para criar as caixas.',
            ],
            [
                'nome' => 'Caixa: Atualizar',
                'slug' => Permissao::CAIXA_UPDATE,
                'descricao' => 'Permissão para atualizar as caixas cadastradas.',
            ],
            [
                'nome' => 'Caixa: Excluir',
                'slug' => Permissao::CAIXA_DELETE,
                'descricao' => 'Permissão para excluir as caixas cadastradas.',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesProcesso()
    {
        return LazyCollection::make([
            [
                'nome' => 'Processo: Visualizar todos',
                'slug' => Permissao::PROCESSO_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todos os processos cadastrados.',
            ],
            [
                'nome' => 'Processo: Visualizar',
                'slug' => Permissao::PROCESSO_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes os processos cadastrados.',
            ],
            [
                'nome' => 'Processo: Criar',
                'slug' => Permissao::PROCESSO_CREATE,
                'descricao' => 'Permissão para criar os processos.',
            ],
            [
                'nome' => 'Processo: Atualizar',
                'slug' => Permissao::PROCESSO_UPDATE,
                'descricao' => 'Permissão para atualizar os processos cadastrados.',
            ],
            [
                'nome' => 'Processo: Excluir',
                'slug' => Permissao::PROCESSO_DELETE,
                'descricao' => 'Permissão para excluir os processos cadastrados.',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesMoverProcesso()
    {
        return LazyCollection::make([
            [
                'nome' => 'Mover Processo: Criar',
                'slug' => Permissao::MOVER_PROCESSO_CREATE,
                'descricao' => 'Permissão para movimentar processos entre as caixas.',
            ],
        ]);
    }

    /**
     * Permissões do usuário do arquivo para administração das solicitações.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesSolicitacao()
    {
        return LazyCollection::make([
            [
                'nome' => 'Solicitação: Usuário do arquivo visualizar todas',
                'slug' => Permissao::SOLICITACAO_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as solicitações cadastradas sob o prisma do usuário do arquivo.',
            ],
            [
                'nome' => 'Solicitação: Usuário do arquivo criar',
                'slug' => Permissao::SOLICITACAO_CREATE,
                'descricao' => 'Permissão para criar as solicitações sob o prisma do usuário do arquivo.',
            ],
            [
                'nome' => 'Solicitação: Usuário do arquivo atualizar',
                'slug' => Permissao::SOLICITACAO_UPDATE,
                'descricao' => 'Permissão para atualizar as solicitações cadastradas sob o prisma do usuário do arquivo.',
            ],
            [
                'nome' => 'Solicitação: Usuário do arquivo excluir',
                'slug' => Permissao::SOLICITACAO_DELETE,
                'descricao' => 'Permissão para excluir as solicitações cadastrados e ainda não entregues sob o prisma do usuário do arquivo.',
            ],
        ]);
    }

    /**
     * Permissões do usuário externo ao arquivo para administração das
     * solicitações.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesSolicitacaoExterna()
    {
        return LazyCollection::make([
            [
                'nome' => 'Solicitação: Usuário externo visualizar todas',
                'slug' => Permissao::SOLICITACAO_EXTERNA_VIEW_ANY,
                'descricao' => 'Permissão para o usuário externo ao arquivo visualizar todas as solicitações cadastradas para a sua lotação.',
            ],
            [
                'nome' => 'Solicitação: Usuário externo criar',
                'slug' => Permissao::SOLICITACAO_EXTERNA_CREATE,
                'descricao' => 'Permissão para o usuário externo ao arquivo criar as solicitações destinadas à sua lotação.',
            ],
            [
                'nome' => 'Solicitação: Usuário externo excluir',
                'slug' => Permissao::SOLICITACAO_EXTERNA_DELETE,
                'descricao' => 'Permissão para o usuário externo excluir as solicitações cadastrados e ainda não recebidas de sua lotação.',
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissoesGuia()
    {
        return LazyCollection::make([
            [
                'nome' => 'Guia de remessa: Visualizar todas',
                'slug' => Permissao::GUIA_VIEW_ANY,
                'descricao' => 'Permissão para visualizar todas as guias de remessa cadastradas.',
            ],
            [
                'nome' => 'Guia de remessa: Visualizar',
                'slug' => Permissao::GUIA_VIEW,
                'descricao' => 'Permissão para visualizar em detalhes as guias de remessa cadastradas.',
            ],
        ]);
    }
}
