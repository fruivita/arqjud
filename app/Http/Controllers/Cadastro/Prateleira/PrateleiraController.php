<?php

namespace App\Http\Controllers\Cadastro\Prateleira;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Prateleira\StorePrateleiraRequest;
use App\Http\Requests\Cadastro\Prateleira\UpdatePrateleiraRequest;
use App\Http\Resources\Caixa\CaixaCollection;
use App\Http\Resources\Estante\EstanteResource;
use App\Http\Resources\Prateleira\PrateleiraCollection;
use App\Http\Resources\Prateleira\PrateleiraResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Prateleira;
use App\Pipes\Caixa\JoinLocalidadeCriadora;
use App\Pipes\Caixa\Order as CaixaOrder;
use App\Pipes\Prateleira\JoinLocalidade;
use App\Pipes\Prateleira\Order;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PrateleiraController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Prateleira::class);

        return Inertia::render('Cadastro/Prateleira/Index', [
            'prateleiras' => fn () => PrateleiraCollection::make(
                Pipeline::make()
                    ->send(Prateleira::withCount(['caixas'])->with('estante.sala.andar.predio.localidade'))
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
     * @param  \App\Models\Estante  $estante
     * @return \Inertia\Response
     */
    public function create(Estante $estante)
    {
        $this->authorize(Policy::Create->value, Prateleira::class);

        return Inertia::render('Cadastro/Prateleira/Create', [
            'ultima_insercao' => fn () => PrateleiraResource::make($estante->prateleiras()->latest()->first()),
            'estante' => fn () => EstanteResource::make($estante->load('sala.andar.predio.localidade')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Prateleira\StorePrateleiraRequest  $request
     * @param  \App\Models\Estante  $estante
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePrateleiraRequest $request, Estante $estante)
    {
        $prateleira = new Prateleira();

        $prateleira->numero = $request->input('numero');
        $prateleira->descricao = $request->input('descricao');

        $salvo = $estante->prateleiras()->save($prateleira);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Prateleira  $prateleira
     * @return \Inertia\Response
     */
    public function edit(Prateleira $prateleira)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Prateleira::class);

        return Inertia::render('Cadastro/Prateleira/Edit', [
            'prateleira' => fn () => PrateleiraResource::make($prateleira->load('estante.sala.andar.predio.localidade')),
            'caixas' => fn () => CaixaCollection::make(
                Pipeline::make()
                    ->send(Caixa::with(['localidadeCriadora'])->withCount(['volumes'])->whereBelongsTo($prateleira))
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
     * @param  \App\Http\Requests\Cadastro\Prateleira\UpdatePrateleiraRequest  $request
     * @param  \App\Models\Prateleira  $prateleira
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePrateleiraRequest $request, Prateleira $prateleira)
    {
        $prateleira->numero = $request->input('numero');
        $prateleira->descricao = $request->input('descricao');

        $salvo = $prateleira->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Prateleira  $prateleira
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Prateleira $prateleira)
    {
        $this->authorize(Policy::Delete->value, $prateleira);

        $excluido = $prateleira->delete();

        return back()->with($this->feedback($excluido));
    }
}
