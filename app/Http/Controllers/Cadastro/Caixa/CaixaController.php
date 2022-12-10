<?php

namespace App\Http\Controllers\Cadastro\Caixa;

use App\Enums\Policy;
use App\Filters\Caixa\JoinLocalidade;
use App\Filters\Caixa\Order;
use App\Filters\Search;
use App\Filters\VolumeCaixa\Order as VolumeCaixaOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Caixa\StoreCaixaRequest;
use App\Http\Requests\Cadastro\Caixa\UpdateCaixaRequest;
use App\Http\Resources\Caixa\CaixaCollection;
use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Caixa;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\VolumeCaixa;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

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
                    ->send(Caixa::withCount(['volumes'])->with(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora']))
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
     * @param  \App\Models\Prateleira  $prateleira
     * @return \Inertia\Response
     */
    public function create(Prateleira $prateleira)
    {
        $this->authorize(Policy::Create->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Create', [
            'ultima_insercao' => fn () => CaixaResource::make($prateleira->caixas()->with('localidadeCriadora')->latest()->first()),
            'prateleira' => fn () => PrateleiraResource::make($prateleira->load('estante.sala.andar.predio.localidade')),
            // @todo melhorar esse limite
            'localidades' => fn () => LocalidadeOnlyResource::collection(Localidade::limit(10)->get()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Caixa\StoreCaixaRequest  $request
     * @param  \App\Models\Prateleira  $prateleira
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCaixaRequest $request, Prateleira $prateleira)
    {
        $caixa = new Caixa();

        $caixa->numero = $request->integer('numero');
        $caixa->ano = $request->integer('ano');
        $caixa->guarda_permanente = $request->boolean('guarda_permanente');
        $caixa->complemento = $request->input('complemento');
        $caixa->descricao = $request->input('descricao');
        $caixa->localidade_criadora_id = $request->integer('localidade_criadora_id');

        $salvo = $prateleira->caixas()->save($caixa);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Inertia\Response
     */
    public function edit(Caixa $caixa)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Edit', [
            'caixa' => fn () => CaixaResource::make($caixa->load(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora'])),
            'volumes_caixa' => fn () => VolumeCaixaCollection::make(
                Pipeline::make()
                    ->send(VolumeCaixa::withCount(['processos'])->whereBelongsTo($caixa))
                    ->through([VolumeCaixaOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Caixa\UpdateCaixaRequest  $request
     * @param  \App\Models\Caixa  $caixa
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

        $salvo = $caixa->atualizar();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Caixa $caixa)
    {
        $this->authorize(Policy::Delete->value, $caixa);

        $excluido = $caixa->delete();

        return back()->with($this->feedback($excluido));
    }
}
