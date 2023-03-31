<?php

namespace App\Http\Controllers\Cadastro\TipoProcesso;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\TipoProcesso\StoreTipoProcessoRequest;
use App\Http\Requests\Cadastro\TipoProcesso\UpdateTipoProcessoRequest;
use App\Http\Resources\Caixa\CaixaCollection;
use App\Http\Resources\TipoProcesso\TipoProcessoCollection;
use App\Http\Resources\TipoProcesso\TipoProcessoEditResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Caixa;
use App\Models\TipoProcesso;
use App\Pipes\Caixa\JoinLocalidadeCriadora;
use App\Pipes\Caixa\Order as CaixaOrder;
use App\Pipes\Search;
use App\Pipes\TipoProcesso\Order;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class TipoProcessoController extends Controller
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
        $this->authorize(Policy::ViewAny->value, TipoProcesso::class);

        return Inertia::render('Cadastro/TipoProcesso/Index', [
            'tipos_processo' => fn () => TipoProcessoCollection::make(
                Pipeline::make()
                    ->send(TipoProcesso::withCount(['caixas']))
                    ->through([Order::class, Search::class])
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
    public function create()
    {
        $this->authorize(Policy::Create->value, TipoProcesso::class);

        return Inertia::render('Cadastro/TipoProcesso/Create', [
            'ultima_insercao' => fn () => TipoProcessoEditResource::make(TipoProcesso::latest()->first()),
            'links' => fn () => ['store' => route('cadastro.tipo-processo.store')],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreTipoProcessoRequest $request)
    {
        $tipo_processo = new TipoProcesso();

        $tipo_processo->nome = $request->input('nome');
        $tipo_processo->descricao = $request->input('descricao');

        $salvo = $tipo_processo->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(TipoProcesso $tipo_processo)
    {
        $this->authorize(Policy::ViewOrUpdate->value, TipoProcesso::class);

        return Inertia::render('Cadastro/TipoProcesso/Edit', [
            'tipo_processo' => fn () => TipoProcessoEditResource::make($tipo_processo),
            'caixas' => fn () => CaixaCollection::make(
                Pipeline::make()
                    ->send(Caixa::with(['localidadeCriadora'])->withCount(['processos'])->whereBelongsTo($tipo_processo))
                    ->through([JoinLocalidadeCriadora::class, CaixaOrder::class])
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
    public function update(UpdateTipoProcessoRequest $request, TipoProcesso $tipo_processo)
    {
        $tipo_processo->nome = $request->input('nome');
        $tipo_processo->descricao = $request->input('descricao');

        $salvo = $tipo_processo->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TipoProcesso $tipo_processo)
    {
        $this->authorize(Policy::Delete->value, $tipo_processo);

        $excluido = $tipo_processo->delete();

        return back()->with($this->feedback($excluido));
    }
}
