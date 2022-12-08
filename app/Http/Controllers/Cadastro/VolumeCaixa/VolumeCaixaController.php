<?php

namespace App\Http\Controllers\Cadastro\VolumeCaixa;

use App\Enums\Policy;
use App\Filters\Processo\Order as ProcessoOrder;
use App\Filters\Search;
use App\Filters\VolumeCaixa\JoinLocalidade;
use App\Filters\VolumeCaixa\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\VolumeCaixa\PostVolumeCaixaRequest;
use App\Http\Resources\Processo\ProcessoCollection;
use App\Http\Resources\VolumeCaixa\VolumeCaixaCollection;
use App\Http\Resources\VolumeCaixa\VolumeCaixaResource;
use App\Models\Processo;
use App\Models\VolumeCaixa;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use MichaelRubel\EnhancedPipeline\Pipeline;
use Inertia\Inertia;

class VolumeCaixaController extends Controller
{
    use ComFeedback;
    use ComPaginacaoEmCache;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
                    ->paginate($this->perPage(request()->query('per_page')))
            )->additional(['meta' => [
                'termo' => request()->query('termo'),
                'order' => request()->query('order'),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VolumeCaixa  $volume_caixa
     * @return \Illuminate\Http\Response
     */
    public function edit(VolumeCaixa $volume_caixa)
    {
        $this->authorize(Policy::ViewOrUpdate->value, VolumeCaixa::class);

        return Inertia::render('Cadastro/VolumeCaixa/Edit', [
            'volume_caixa' => fn () => VolumeCaixaResource::make($volume_caixa->load(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora'])),
            'processos' => fn () => ProcessoCollection::make(
                Pipeline::make()
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->whereBelongsTo($volume_caixa))
                    ->through([ProcessoOrder::class])
                    ->thenReturn()
                    ->paginate($this->perPage(request()->query('per_page')))
            )->additional(['meta' => [
                'order' => request()->query('order'),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\VolumeCaixa\PostVolumeCaixaRequest  $request
     * @param  \App\Models\VolumeCaixa  $volume_caixa
     * @return \Illuminate\Http\Response
     */
    public function update(PostVolumeCaixaRequest $request, VolumeCaixa $volume_caixa)
    {
        $volume_caixa->numero = $request->input('numero');
        $volume_caixa->descricao = $request->input('descricao');

        $salvo = $volume_caixa->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VolumeCaixa  $volume_caixa
     * @return \Illuminate\Http\Response
     */
    public function destroy(VolumeCaixa $volume_caixa)
    {
        $this->authorize(Policy::Delete->value, $volume_caixa);

        $excluido = $volume_caixa->delete();

        return back()->with($this->feedback($excluido));
    }
}
