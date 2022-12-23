<?php

namespace App\Http\Controllers\Cadastro\Sala;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Sala\StoreSalaRequest;
use App\Http\Requests\Cadastro\Sala\UpdateSalaRequest;
use App\Http\Resources\Andar\AndarEditResource;
use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Estante\EstanteCollection;
use App\Http\Resources\Sala\SalaCollection;
use App\Http\Resources\Sala\SalaEditResource;
use App\Http\Resources\Sala\SalaResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Andar;
use App\Models\Estante;
use App\Models\Sala;
use App\Pipes\Estante\Order as EstanteOrder;
use App\Pipes\Sala\JoinLocalidade;
use App\Pipes\Sala\Order;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class SalaController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Sala::class);

        return Inertia::render('Cadastro/Sala/Index', [
            'salas' => fn () => SalaCollection::make(
                Pipeline::make()
                    ->send(Sala::withCount(['estantes'])->with('andar.predio.localidade'))
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
     * @param  \App\Models\Andar  $andar
     * @return \Inertia\Response
     */
    public function create(Andar $andar)
    {
        $this->authorize(Policy::Create->value, Sala::class);

        return Inertia::render('Cadastro/Sala/Create', [
            'ultima_insercao' => fn () => SalaEditResource::make($andar->salas()->latest()->first()),
            'andar' => fn () => AndarEditResource::make($andar->load('predio.localidade')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Sala\StoreSalaRequest  $request
     * @param  \App\Models\Andar  $andar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreSalaRequest $request, Andar $andar)
    {
        $salvo = Sala::criar(
            $request->input('numero'),
            $andar,
            $request->input('descricao')
        );

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sala  $sala
     * @return \Inertia\Response
     */
    public function edit(Sala $sala)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Sala::class);

        return Inertia::render('Cadastro/Sala/Edit', [
            'sala' => fn () => SalaEditResource::make($sala->load('andar.predio.localidade')),
            'estantes' => fn () => EstanteCollection::make(
                Pipeline::make()
                    ->send(Estante::withCount(['prateleiras'])->whereBelongsTo($sala))
                    ->through([EstanteOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Sala\UpdateSalaRequest  $request
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateSalaRequest $request, Sala $sala)
    {
        $sala->numero = $request->input('numero');
        $sala->descricao = $request->input('descricao');

        $salvo = $sala->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sala  $sala
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Sala $sala)
    {
        $this->authorize(Policy::Delete->value, $sala);

        $excluido = $sala->delete();

        return back()->with($this->feedback($excluido));
    }
}
