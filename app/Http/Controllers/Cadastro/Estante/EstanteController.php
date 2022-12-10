<?php

namespace App\Http\Controllers\Cadastro\Estante;

use App\Enums\Policy;
use App\Filters\Estante\JoinLocalidade;
use App\Filters\Estante\Order;
use App\Filters\Prateleira\Order as PrateleiraOrder;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Estante\StoreEstanteRequest;
use App\Http\Requests\Cadastro\Estante\UpdateEstanteRequest;
use App\Http\Resources\Estante\EstanteCollection;
use App\Http\Resources\Estante\EstanteResource;
use App\Http\Resources\Prateleira\PrateleiraCollection;
use App\Http\Resources\Sala\SalaResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Estante;
use App\Models\Prateleira;
use App\Models\Sala;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

class EstanteController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Estante::class);

        return Inertia::render('Cadastro/Estante/Index', [
            'estantes' => fn () => EstanteCollection::make(
                Pipeline::make()
                    ->send(Estante::withCount(['prateleiras'])->with('sala.andar.predio.localidade'))
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
     * @param  \App\Models\Sala  $sala
     * @return \Inertia\Response
     */
    public function create(Sala $sala)
    {
        $this->authorize(Policy::Create->value, Estante::class);

        return Inertia::render('Cadastro/Estante/Create', [
            'ultima_insercao' => fn () => EstanteResource::make($sala->estantes()->latest()->first()),
            'sala' => fn () => SalaResource::make($sala->load('andar.predio.localidade')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Estante\StoreEstanteRequest  $request
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreEstanteRequest $request, Sala $sala)
    {
        $salvo = Estante::criar(
            $request->input('numero'),
            $sala,
            $request->input('descricao')
        );

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Estante  $estante
     * @return \Inertia\Response
     */
    public function edit(Estante $estante)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Estante::class);

        return Inertia::render('Cadastro/Estante/Edit', [
            'estante' => fn () => EstanteResource::make($estante->load('sala.andar.predio.localidade')),
            'prateleiras' => fn () => PrateleiraCollection::make(
                Pipeline::make()
                    ->send(Prateleira::withCount(['caixas'])->whereBelongsTo($estante))
                    ->through([PrateleiraOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Estante\UpdateEstanteRequest  $request
     * @param  \App\Models\Estante  $estante
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateEstanteRequest $request, Estante $estante)
    {
        $estante->numero = $request->input('numero');
        $estante->descricao = $request->input('descricao');

        $salvo = $estante->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Estante  $estante
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Estante $estante)
    {
        $this->authorize(Policy::Delete->value, $estante);

        $excluido = $estante->delete();

        return back()->with($this->feedback($excluido));
    }
}
