<?php

namespace App\Http\Controllers\Cadastro\VolumeCaixa;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\VolumeCaixa\StoreVolumeCaixaRequest;
use App\Http\Requests\Cadastro\VolumeCaixa\UpdateVolumeCaixaRequest;
use App\Http\Resources\Caixa\CaixaEditResource;
use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\Processo\ProcessoCollection;
use App\Http\Resources\VolumeCaixa\VolumeCaixaCollection;
use App\Http\Resources\VolumeCaixa\VolumeCaixaEditResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Caixa;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use App\Pipes\Processo\Order as ProcessoOrder;
use App\Pipes\Search;
use App\Pipes\VolumeCaixa\JoinLocalidade;
use App\Pipes\VolumeCaixa\Order;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class VolumeCaixaController extends Controller
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
        $this->authorize(Policy::ViewAny->value, VolumeCaixa::class);

        return Inertia::render('Cadastro/VolumeCaixa/Index', [
            'volumes' => fn () => VolumeCaixaCollection::make(
                Pipeline::make()
                    ->send(VolumeCaixa::withCount(['processos'])->with(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora']))
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
     * @param  \App\Models\Caixa  $caixa
     * @return \Inertia\Response
     */
    public function create(Caixa $caixa)
    {
        $this->authorize(Policy::Create->value, VolumeCaixa::class);

        return Inertia::render('Cadastro/VolumeCaixa/Create', [
            'ultima_insercao' => fn () => VolumeCaixaEditResource::make($caixa->volumes()->latest()->first()),
            'caixa' => fn () => CaixaEditResource::make($caixa->load(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora'])),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\VolumeCaixa\StoreVolumeCaixaRequest  $request
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreVolumeCaixaRequest $request, Caixa $caixa)
    {
        $volume_caixa = new VolumeCaixa();

        $volume_caixa->numero = $request->integer('numero');
        $volume_caixa->descricao = $request->input('descricao');

        $salvo = $caixa->volumes()->save($volume_caixa);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VolumeCaixa  $volume_caixa
     * @return \Inertia\Response
     */
    public function edit(VolumeCaixa $volume_caixa)
    {
        $this->authorize(Policy::ViewOrUpdate->value, VolumeCaixa::class);

        return Inertia::render('Cadastro/VolumeCaixa/Edit', [
            'volume_caixa' => fn () => VolumeCaixaEditResource::make($volume_caixa->load(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora'])),
            'processos' => fn () => ProcessoCollection::make(
                Pipeline::make()
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->whereBelongsTo($volume_caixa))
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
     * @param  \App\Http\Requests\Cadastro\VolumeCaixa\UpdateVolumeCaixaRequest  $request
     * @param  \App\Models\VolumeCaixa  $volume_caixa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateVolumeCaixaRequest $request, VolumeCaixa $volume_caixa)
    {
        $volume_caixa->numero = $request->integer('numero');
        $volume_caixa->descricao = $request->input('descricao');

        $salvo = $volume_caixa->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VolumeCaixa  $volume_caixa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(VolumeCaixa $volume_caixa)
    {
        $this->authorize(Policy::Delete->value, $volume_caixa);

        $excluido = $volume_caixa->delete();

        return back()->with($this->feedback($excluido));
    }
}
