<?php

namespace App\Http\Controllers\Solicitacao;

use App\Enums\Policy;
use App\Filters\Search;
use App\Filters\Solicitacao\JoinAll;
use App\Filters\Solicitacao\Order;
use App\Http\Controllers\Controller;
use App\Http\Resources\Lotacao\LotacaoOnlyResource;
use App\Http\Resources\Solicitacao\CounterResource;
use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Lotacao;
use App\Models\Solicitacao;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
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
            'lotacao' => fn () => LotacaoOnlyResource::make(
                Lotacao::find(auth()->user()->lotacao_id)
            ),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Remessa\StoreRemessaRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        // $processos = Arr::pluck($request->input('processos'), 'numero');

        // $salvo = SolicitarRemessa::make()->solicitar(
        //     $processos,
        //     intval(auth()->user()->id),
        //     intval(auth()->user()->lotacao_id),
        // );

        // RemessaSolicitadaPeloUsuario::dispatchIf($salvo, $processos);

        // return back()->with(...$this->feedback($salvo));
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
