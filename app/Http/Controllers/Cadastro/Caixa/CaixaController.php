<?php

namespace App\Http\Controllers\Cadastro\Caixa;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Caixa\StoreCaixaRequest;
use App\Http\Requests\Cadastro\Caixa\UpdateCaixaRequest;
use App\Http\Resources\Caixa\CaixaCollection;
use App\Http\Resources\Caixa\CaixaEditResource;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraEditResource;
use App\Http\Resources\Processo\ProcessoCollection;
use App\Http\Resources\TipoProcesso\TipoProcessoOnlyResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Processo;
use App\Models\TipoProcesso;
use App\Pipes\Caixa\JoinLocalidade;
use App\Pipes\Caixa\Order;
use App\Pipes\Processo\Order as ProcessoOrder;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class CaixaController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Index', [
            'caixas' => fn () => CaixaCollection::make(
                Pipeline::make()
                    ->send(Caixa::withCount(['processos'])->with(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora', 'tipoProcesso']))
                    ->through([JoinLocalidade::class, Order::class, Search::class])
                    ->thenReturn()
                    ->paginate($this->perPage())
            )->additional(['meta' => [
                'termo' => request()->query('termo'),
                'order' => request()->query('order'),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create(Prateleira $prateleira)
    {
        $this->authorize(Policy::Create->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Create', [
            'ultima_insercao' => fn () => CaixaEditResource::make($prateleira->caixas()->with('localidadeCriadora')->latest()->first()),
            'prateleira' => fn () => PrateleiraEditResource::make($prateleira->load('estante.sala.andar.predio.localidade')),
            'localidades' => fn () => LocalidadeOnlyResource::collection(Localidade::all()),
            'tipos_processo' => fn () => TipoProcessoOnlyResource::collection(TipoProcesso::all()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCaixaRequest $request, Prateleira $prateleira)
    {
        $caixa = new Caixa();

        $caixa->numero = $request->integer('numero');
        $caixa->ano = $request->integer('ano');
        $caixa->guarda_permanente = $request->boolean('guarda_permanente');
        $caixa->descricao = $request->input('descricao');
        $caixa->localidade_criadora_id = $request->integer('localidade_criadora_id');
        $caixa->tipo_processo_id = $request->integer('tipo_processo_id');

        $salvo = $prateleira->caixas()->save($caixa);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Caixa $caixa)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Edit', [
            'caixa' => fn () => CaixaEditResource::make($caixa->load(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora', 'tipoProcesso'])),
            'tipos_processo' => fn () => TipoProcessoOnlyResource::collection(TipoProcesso::all()),
            'processos' => fn () => ProcessoCollection::make(
                Pipeline::make()
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->whereBelongsTo($caixa))
                    ->through([ProcessoOrder::class])
                    ->thenReturn()
                    ->paginate($this->perPage())
            )->additional(['meta' => [
                'order' => request()->query('order'),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCaixaRequest $request, Caixa $caixa)
    {
        $caixa->numero = $request->integer('numero');
        $caixa->ano = $request->integer('ano');
        $caixa->guarda_permanente = $request->boolean('guarda_permanente');
        $caixa->complemento = $request->input('complemento');
        $caixa->descricao = $request->input('descricao');
        $caixa->localidade_criadora_id = $request->integer('localidade_criadora_id');
        $caixa->tipo_processo_id = $request->integer('tipo_processo_id');

        $salvo = $caixa->atualizar();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Caixa $caixa)
    {
        $this->authorize(Policy::Delete->value, $caixa);

        $excluido = $caixa->delete();

        return back()->with($this->feedback($excluido));
    }
}
