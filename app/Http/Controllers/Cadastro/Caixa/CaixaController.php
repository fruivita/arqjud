<?php

namespace App\Http\Controllers\Cadastro\Caixa;

use App\Enums\Policy;
use App\Filters\Caixa\JoinLocalidade;
use App\Filters\Caixa\Order;
use App\Filters\Search;
use App\Filters\VolumeCaixa\Order as VolumeCaixaOrder;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Caixa\PostCaixaRequest;
use App\Http\Resources\Caixa\CaixaCollection;
use App\Http\Resources\Caixa\CaixaResource;
use App\Http\Resources\VolumeCaixa\VolumeCaixaCollection;
use App\Models\Caixa;
use App\Models\VolumeCaixa;
use App\Pipes\Caixa\Atualizar;
use App\Pipes\Caixa\SetGPProcessos;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

class CaixaController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Index', [
            'caixas' => CaixaCollection::make(
                Pipeline::make()
                    ->send(Caixa::withCount(['volumes'])->with(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora']))
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
     * Display the specified resource.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function show(Caixa $caixa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function edit(Caixa $caixa)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Edit', [
            'caixa' => fn () => CaixaResource::make($caixa->load(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora'])),
            'volumes_caixa' => VolumeCaixaCollection::make(
                Pipeline::make()
                    ->send(VolumeCaixa::withCount(['processos'])->whereBelongsTo($caixa))
                    ->through([VolumeCaixaOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Caixa\PostCaixaRequest  $request
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function update(PostCaixaRequest $request, Caixa $caixa)
    {
        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($caixa)
            ->through([Atualizar::class, SetGPProcessos::class])
            ->onFailure(function ($data, $exception) {
                return false;
            })->thenReturn();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Caixa $caixa)
    {
        $this->authorize(Policy::Delete->value, $caixa);

        $excluido = $caixa->delete();

        return back()->with($this->feedback($excluido));
    }
}
