<?php

namespace App\Services\Menu;

use App\Enums\Policy;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Guia;
use App\Models\Localidade;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Processo;
use App\Models\Sala;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Models\VolumeCaixa;
use Illuminate\Support\Facades\Route;

/**
 * @see https://m.dotdev.co/design-pattern-service-layer-with-laravel-5-740ff0a7b65f
 * @see https://blackdeerdev.com/laravel-services-pattern/
 */
final class Menu implements MenuInterface
{
    /**
     * Create new class instance.
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function gerar()
    {
        return collect()
            ->when(
                $this->linksGrupoAtendimento(),
                fn ($collection, $links) => $collection->push(['nome' => __('Atendimentos'), 'links' => $links])
            )
            ->when(
                $this->linksGrupoSolicitacao(),
                fn ($collection, $links) => $collection->push(['nome' => __('Solicitações de processos'), 'links' => $links])
            )
            ->when(
                $this->linksGrupoMovimentacao(),
                fn ($collection, $links) => $collection->push(['nome' => __('Movimentações'), 'links' => $links])
            )
            ->when(
                $this->linksGrupoCadastro(),
                fn ($collection, $links) => $collection->push(['nome' => __('Cadastros'), 'links' => $links])
            )
            ->when(
                $this->linksGrupoAutorizacao(),
                fn ($collection, $links) => $collection->push(['nome' => __('Autorizações'), 'links' => $links])
            )
            ->when(
                $this->linksGrupoAdministracao(),
                fn ($collection, $links) => $collection->push(['nome' => __('Administração'), 'links' => $links])
            )
            ->toArray();
    }

    /**
     * @return array
     */
    private function linksGrupoAtendimento()
    {
        return collect()
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Solicitacao::class),
                fn ($collection) => $collection->push([
                    'icone' => 'signpost-2',
                    'href' => route('atendimento.solicitar-processo.index'),
                    'texto' => __('Solicitações'),
                    'ativo' => Route::is('atendimento.solicitar-processo.index'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::Create->value, Solicitacao::class),
                fn ($collection) => $collection->push([
                    'icone' => 'signpost',
                    'href' => route('atendimento.solicitar-processo.create'),
                    'texto' => __('Solicitar processo'),
                    'ativo' => Route::is('atendimento.solicitar-processo.create'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::Update->value, Solicitacao::class),
                fn ($collection) => $collection->push(
                    [
                        'icone' => 'cart',
                        'href' => route('atendimento.entregar-processo.create'),
                        'texto' => __('Entregar processos'),
                        'ativo' => Route::is('atendimento.entregar-processo.*'),
                    ],
                    [
                        'icone' => 'safe',
                        'href' => route('atendimento.receber-processo.create'),
                        'texto' => __('Receber processos'),
                        'ativo' => Route::is('atendimento.receber-processo.*'),
                    ]
                )
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Guia::class),
                fn ($collection) => $collection->push([
                    'icone' => 'files',
                    'href' => route('atendimento.guia.index'),
                    'texto' => __('Guias'),
                    'ativo' => Route::is('atendimento.guia.*'),
                ])
            )
            ->toArray();
    }

    /**
     * @return array
     */
    private function linksGrupoSolicitacao()
    {
        return collect()
            ->when(
                auth()->user()->can(Policy::ExternoCreate->value, Solicitacao::class),
                fn ($collection) => $collection->push([
                    'icone' => 'signpost',
                    'href' => route('solicitacao.create'),
                    'texto' => __('Solicitar'),
                    'ativo' => Route::is('solicitacao.create'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ExternoViewAny->value, Solicitacao::class),
                fn ($collection) => $collection->push([
                    'icone' => 'signpost-2',
                    'href' => route('solicitacao.index'),
                    'texto' => __('Solicitações'),
                    'ativo' => Route::is('solicitacao.index'),
                ])
            )
            ->toArray();
    }

    /**
     * @return array
     */
    private function linksGrupoMovimentacao()
    {
        return collect()
            ->when(
                auth()->user()->can(Policy::MoverProcessoCreate->value),
                fn ($collection) => $collection->push([
                    'icone' => 'boxes',
                    'href' => route('movimentacao.entre-caixas.create'),
                    'texto' => __('Entre caixas'),
                    'ativo' => Route::is('movimentacao.entre-caixas.*'),
                ])
            )
            ->toArray();
    }

    /**
     * @return array
     */
    private function linksGrupoCadastro()
    {
        return collect()
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Localidade::class),
                fn ($collection) => $collection->push([
                    'icone' => 'pin-map',
                    'href' => route('cadastro.localidade.index'),
                    'texto' => __('Localidades'),
                    'ativo' => Route::is('cadastro.localidade.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Predio::class),
                fn ($collection) => $collection->push([
                    'icone' => 'buildings',
                    'href' => route('cadastro.predio.index'),
                    'texto' => __('Prédios'),
                    'ativo' => Route::is('cadastro.predio.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Andar::class),
                fn ($collection) => $collection->push([
                    'icone' => 'layers',
                    'href' => route('cadastro.andar.index'),
                    'texto' => __('Andares'),
                    'ativo' => Route::is('cadastro.andar.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Sala::class),
                fn ($collection) => $collection->push([
                    'icone' => 'door-closed',
                    'href' => route('cadastro.sala.index'),
                    'texto' => __('Salas'),
                    'ativo' => Route::is('cadastro.sala.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Estante::class),
                fn ($collection) => $collection->push([
                    'icone' => 'bookshelf',
                    'href' => route('cadastro.estante.index'),
                    'texto' => __('Estantes'),
                    'ativo' => Route::is('cadastro.estante.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Prateleira::class),
                fn ($collection) => $collection->push([
                    'icone' => 'list-nested',
                    'href' => route('cadastro.prateleira.index'),
                    'texto' => __('Prateleiras'),
                    'ativo' => Route::is('cadastro.prateleira.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Caixa::class),
                fn ($collection) => $collection->push([
                    'icone' => 'box2',
                    'href' => route('cadastro.caixa.index'),
                    'texto' => __('Caixas'),
                    'ativo' => Route::is('cadastro.caixa.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, VolumeCaixa::class),
                fn ($collection) => $collection->push([
                    'icone' => 'boxes',
                    'href' => route('cadastro.volume-caixa.index'),
                    'texto' => __('Volumes das caixas'),
                    'ativo' => Route::is('cadastro.volume-caixa.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Processo::class),
                fn ($collection) => $collection->push([
                    'icone' => 'journal-bookmark',
                    'href' => route('cadastro.processo.index'),
                    'texto' => __('Processos'),
                    'ativo' => Route::is('cadastro.processo.*'),
                ])
            )
            ->toArray();
    }

    /**
     * @return array
     */
    private function linksGrupoAutorizacao()
    {
        return collect()
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Usuario::class),
                fn ($collection) => $collection->push([
                    'icone' => 'people',
                    'href' => route('autorizacao.usuario.index'),
                    'texto' => __('Usuários'),
                    'ativo' => Route::is('autorizacao.usuario.*'),
                ])
            )
            ->toArray();
    }

    /**
     * @return array
     */
    private function linksGrupoAdministracao()
    {
        return collect()
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Perfil::class),
                fn ($collection) => $collection->push([
                    'icone' => 'award',
                    'href' => route('administracao.perfil.index'),
                    'texto' => __('Perfis'),
                    'ativo' => Route::is('administracao.perfil.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Permissao::class),
                fn ($collection) => $collection->push([
                    'icone' => 'vector-pen',
                    'href' => route('administracao.permissao.index'),
                    'texto' => __('Permissões'),
                    'ativo' => Route::is('administracao.permissao.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ViewAny->value, Lotacao::class),
                fn ($collection) => $collection->push([
                    'icone' => 'buildings',
                    'href' => route('administracao.lotacao.index'),
                    'texto' => __('Lotações'),
                    'ativo' => Route::is('administracao.lotacao.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::ImportacaoCreate->value),
                fn ($collection) => $collection->push([
                    'icone' => 'usb-drive',
                    'href' => route('administracao.importacao.create'),
                    'texto' => __('Importar dados'),
                    'ativo' => Route::is('administracao.importacao.*'),
                ])
            )
            ->when(
                auth()->user()->can(Policy::LogViewAny->value),
                fn ($collection) => $collection->push([
                    'icone' => 'file-earmark-text',
                    'href' => route('administracao.log.index'),
                    'texto' => __('Logs de funcionamento'),
                    'ativo' => Route::is('administracao.log.*'),
                ])
            )
            ->toArray();
    }
}
