<?php

namespace App\Http\Controllers\Atendimento;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Atendimento\StoreSolicitacaoRequest;
use App\Http\Resources\Solicitacao\CounterResource;
use App\Http\Resources\Solicitacao\SolicitacaoCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Lotacao;
use App\Models\Solicitacao;
use App\Models\Usuario;
use App\Pipes\Search;
use App\Pipes\Solicitacao\ExcluirSolicitacao;
use App\Pipes\Solicitacao\JoinAll;
use App\Pipes\Solicitacao\NotificarCancelamento;
use App\Pipes\Solicitacao\NotificarSolicitante;
use App\Pipes\Solicitacao\Order;
use App\Pipes\Solicitacao\SolicitarProcesso;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('Atendimento/Solicitacao/Index', [
            'solicitacoes' => fn () => SolicitacaoCollection::make(
                Pipeline::make()
                    ->send(Solicitacao::select('solicitacoes.*')->with(['processo', 'solicitante', 'recebedor', 'remetente', 'rearquivador', 'destino']))
                    ->through([JoinAll::class, Order::class, Search::class])
                    ->thenReturn()
                    ->paginate($this->perPage())
            )->additional(['meta' => [
                'termo' => request()->query('termo'),
                'order' => request()->query('order'),
                'count' => CounterResource::make(
                    Solicitacao::countAll()->toBase()->first()
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
        $this->authorize(Policy::Create->value, Solicitacao::class);

        return Inertia::render('Atendimento/Solicitacao/Create', [
            'links' => fn () => [
                'solicitante' => route('api.solicitacao.solicitante.show'),
                'processo' => route('api.solicitacao.processo.show'),
                'store' => route('atendimento.solicitar-processo.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Atendimento\StoreSolicitacaoRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreSolicitacaoRequest $request)
    {
        $solicitacao = new \stdClass();
        $solicitacao->processos = Arr::pluck($request->input('processos'), 'numero');
        $solicitacao->solicitante = Usuario::findOrFail($request->integer('solicitante_id'));
        $solicitacao->destino = Lotacao::findOrFail($request->integer('destino_id'));

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($solicitacao)
            ->through([
                SolicitarProcesso::class,
                NotificarSolicitante::class,
            ])->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha ao solicitar o processo'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

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
        $this->authorize(Policy::Delete->value, $solicitacao);

        $std = new \stdClass();
        $std->model = $solicitacao;

        $excluido = Pipeline::make()
            ->withTransaction()
            ->send($std)
            ->through([
                ExcluirSolicitacao::class,
                NotificarCancelamento::class,
            ])->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha ao excluir solicitação'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

        return back()->with($this->feedback($excluido));
    }
}
