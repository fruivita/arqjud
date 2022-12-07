<?php

namespace App\Http\Controllers\Cadastro\Sala;

use App\Enums\Policy;
use App\Filters\Estante\Order as EstanteOrder;
use App\Filters\Sala\JoinLocalidade;
use App\Filters\Sala\Order;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Sala\PostSalaRequest;
use App\Http\Resources\Estante\EstanteCollection;
use App\Http\Resources\Sala\SalaCollection;
use App\Http\Resources\Sala\SalaResource;
use App\Models\Estante;
use App\Models\Sala;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Inertia\Inertia;

class SalaController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Sala::class);

        return Inertia::render('Cadastro/Sala/Index', [
            'salas' => SalaCollection::make(
                app(Pipeline::class)
                    ->send(Sala::withCount(['estantes'])->with('andar.predio.localidade'))
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
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\Response
     */
    public function show(Sala $sala)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\Response
     */
    public function edit(Sala $sala)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Sala::class);

        return Inertia::render('Cadastro/Sala/Edit', [
            'sala' => fn () => SalaResource::make($sala->load('andar.predio.localidade')),
            'estantes' => EstanteCollection::make(
                app(Pipeline::class)
                    ->send(Estante::withCount(['prateleiras'])->whereBelongsTo($sala))
                    ->through([EstanteOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Sala\PostSalaRequest  $request
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\Response
     */
    public function update(PostSalaRequest $request, Sala $sala)
    {
        $sala->numero = $request->input('numero');
        $sala->descricao = $request->input('descricao');

        $salvo = $sala->save();

        return back()->with(...$this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sala $sala)
    {
        $this->authorize(Policy::Delete->value, $sala);

        $excluido = $sala->delete();

        return back()->with(...$this->feedback($excluido));
    }
}
