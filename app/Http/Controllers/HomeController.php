<?php

namespace App\Http\Controllers;

use App\Enums\Policy;
use App\Http\Requests\ShowProcessoHomeRequest;
use App\Http\Resources\Processo\ProcessoOnlyResource;
use App\Http\Resources\Solicitacao\CounterResource;
use App\Http\Resources\Solicitacao\SolicitacaoOnlyResource;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Processo;
use App\Models\Solicitacao;
use Illuminate\Support\Collection;
use Inertia\Inertia;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class HomeController extends Controller
{
    use ComPaginacaoEmCache;

    /**
     * Display the specified resource.
     *
     * @return \Inertia\Response
     */
    public function show(ShowProcessoHomeRequest $request)
    {
        return (auth()->user()->can(Policy::View->value, Processo::class))
            ? $this->renderHomeProcesso($request->input('termo'))
            : $this->renderHome();
    }

    /**
     * Renderiza a página home para pesquisa de processo.
     *
     * @param  string|null  $processo número do processo
     * @return \Inertia\Response
     */
    private function renderHomeProcesso(string $processo = null)
    {
        return Inertia::render('Home/HomeProcesso', [
            'processo' => fn () => ProcessoOnlyResource::make(
                $processo
                    ? Processo::with([
                        'solicitacoesAtivas.solicitante',
                        'solicitacoesAtivas.recebedor',
                        'solicitacoesAtivas.remetente',
                        'solicitacoesAtivas.rearquivador',
                        'solicitacoesAtivas.destino',
                        'caixa.prateleira.estante.sala.andar.predio.localidade',
                        'caixa.localidadeCriadora',
                        'processoPai',
                    ])->where('numero', $processo)
                        ->orWhere('numero_antigo', $processo)
                        ->first()
                    : null
            ),
            'links' => fn () => ['search' => route('home.show')],
        ]);
    }

    /**
     * Renderiza a página home com dados sobre a lotação do usuário autenticado.
     *
     * @return \Inertia\Response
     */
    private function renderHome()
    {
        return Inertia::render('Home/Home', [
            'solicitacoes' => fn () => CounterResource::make(
                Solicitacao::countAll()
                    ->where('destino_id', auth()->user()->lotacao_id)
                    ->toBase()
                    ->first()
            )->additional([
                'links' => collect()
                    ->when(auth()->user()->can(Policy::ExternoViewAny->value, Solicitacao::class), function (Collection $collection) {
                        return $collection->put('view_any', route('solicitacao.index'));
                    })
                    ->when(auth()->user()->can(Policy::ExternoCreate->value, Solicitacao::class), function (Collection $collection) {
                        return $collection->put('create', route('solicitacao.create'));
                    })->toArray(),
            ]),
            'disponiveis' => fn () => SolicitacaoOnlyResource::collection(
                Solicitacao::with(['processo', 'solicitante'])
                    ->where('destino_id', auth()->user()->lotacao_id)
                    ->whereNotNull('notificado_em')
                    ->paginate($this->perPage())
            )->preserveQuery(),
        ]);
    }
}
