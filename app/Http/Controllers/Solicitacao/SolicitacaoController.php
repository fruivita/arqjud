<?php

namespace App\Http\Controllers\Solicitacao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Solicitacao\StoreSolicitacaoRequest;
use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Solicitacao\CounterResource;
use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Solicitacao;
use App\Pipes\Search;
use App\Pipes\Solicitacao\JoinAll;
use App\Pipes\Solicitacao\NotificarOperadoresSolicitacao;
use App\Pipes\Solicitacao\Order;
use App\Pipes\Solicitacao\SolicitarProcesso;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class SolicitacaoController extends Controller
{
    use ComFeedback;
    use ComPaginacaoEmCache;

    /**
     * Display a listing of the resource.
     *
     * Apenas as solicitação de processo da própria lotação do usuário
     * autenticado.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        auth()->user()->loadMissing('lotacao'); // @phpstan-ignore-line

        return Inertia::render('Solicitacao/Index', [
            'solicitacoes' => fn () => SolicitacaoCollection::make(
                Pipeline::make()
                    ->send(Solicitacao::select('solicitacoes.*')->with(['processo', 'solicitante', 'recebedor', 'remetente', 'rearquivador', 'lotacaoDestinataria']))
                    ->through([JoinAll::class, Order::class, Search::class])
                    ->thenReturn()
                    ->whereBelongsTo(auth()->user()->lotacao, 'lotacaoDestinataria')
                    ->paginate($this->perPage())
            )->additional(['meta' => [
                'termo' => request()->query('termo'),
                'order' => request()->query('order'),
                'lotacao_destinataria' => LotacaoOnlyResource::make(auth()->user()->lotacao),
                'count' => CounterResource::make(
                    Solicitacao::countAll()
                        ->whereBelongsTo(auth()->user()->lotacao, 'lotacaoDestinataria') // @phpstan-ignore-line
                        ->toBase()
                        ->first()
                ),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize(Policy::ExternoCreate->value, Solicitacao::class);

        return Inertia::render('Solicitacao/Create', [
            'lotacao' => fn () => LotacaoOnlyResource::make(auth()->user()->lotacao),
            'links' => fn () => [
                'search' => route('api.solicitacao.processo.show'),
                'store' => route('solicitacao.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Solicitacao\StoreSolicitacaoRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreSolicitacaoRequest $request)
    {
        auth()->user()->loadMissing('lotacao');
        throw_if(!auth()->user()->lotacao, new \UnexpectedValueException(__('Usuário sem lotação')));

        $solicitacao = new \stdClass();
        $solicitacao->processos = Arr::pluck($request->input('processos'), 'numero');
        $solicitacao->solicitante = auth()->user();
        $solicitacao->destino = auth()->user()->lotacao;

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($solicitacao)
            ->through([
                SolicitarProcesso::class,
                NotificarOperadoresSolicitacao::class,
            ])->thenReturn();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Solicitacao  $solicitacao
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Solicitacao $solicitacao)
    {
        $this->authorize(Policy::ExternoDelete->value, $solicitacao);

        $excluido = $solicitacao->delete();

        return back()->with($this->feedback($excluido));
    }
}
