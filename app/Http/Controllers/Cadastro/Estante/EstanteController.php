<?php

namespace App\Http\Controllers\Cadastro\Estante;

use App\Enums\Policy;
use App\Filters\Estante\JoinLocalidade;
use App\Filters\Estante\Order;
use App\Filters\Prateleira\Order as PrateleiraOrder;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Estante\PostEstanteRequest;
use App\Http\Resources\Estante\EstanteCollection;
use App\Http\Resources\Estante\EstanteResource;
use App\Http\Resources\Prateleira\PrateleiraCollection;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Estante;
use App\Models\Prateleira;
use App\Models\Sala;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

class EstanteController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Estante::class);

        return Inertia::render('Cadastro/Estante/Index', [
            'estantes' => fn () => EstanteCollection::make(
                Pipeline::make()
                    ->send(Estante::withCount(['prateleiras'])->with('sala.andar.predio.localidade'))
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
     * @param  \App\Http\Requests\Cadastro\Estante\PostEstanteRequest  $request
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PostEstanteRequest $request, Sala $sala)
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
     * @return \Illuminate\Http\Response
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
                    ->paginate($this->perPage(request()->query('per_page')))
            )->additional(['meta' => [
                'order' => request()->query('order'),
            ]])->preserveQuery(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Estante\PostEstanteRequest  $request
     * @param  \App\Models\Estante  $estante
     * @return \Illuminate\Http\Response
     */
    public function update(PostEstanteRequest $request, Estante $estante)
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
     * @return \Illuminate\Http\Response
     */
    public function destroy(Estante $estante)
    {
        $this->authorize(Policy::Delete->value, $estante);

        $excluido = $estante->delete();

        return back()->with($this->feedback($excluido));
    }
}
