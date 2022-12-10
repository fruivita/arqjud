<?php

namespace App\Http\Controllers\Cadastro\Andar;

use App\Enums\Policy;
use App\Filters\Andar\JoinLocalidade;
use App\Filters\Andar\Order;
use App\Filters\Sala\Order as SalaOrder;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Andar\StoreAndarRequest;
use App\Http\Requests\Cadastro\Andar\UpdateAndarRequest;
use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Predio\PredioResource;
use App\Http\Resources\Sala\SalaCollection;
use App\Models\Andar;
use App\Models\Predio;
use App\Models\Sala;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

class AndarController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Andar::class);

        return Inertia::render('Cadastro/Andar/Index', [
            'andares' => fn () => AndarCollection::make(
                Pipeline::make()
                    ->send(Andar::withCount(['salas'])->with('predio.localidade'))
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
     * @param  \App\Models\Predio  $predio
     * @return \Inertia\Response
     */
    public function create(Predio $predio)
    {
        $this->authorize(Policy::Create->value, Andar::class);

        return Inertia::render('Cadastro/Andar/Create', [
            'ultima_insercao' => fn () => AndarResource::make($predio->andares()->latest()->first()),
            'predio' => fn () => PredioResource::make($predio->load('localidade')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Andar\StoreAndarRequest  $request
     * @param  \App\Models\Predio  $predio
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreAndarRequest $request, Predio $predio)
    {
        $andar = new Andar();

        $andar->numero = $request->integer('numero');
        $andar->apelido = $request->input('apelido');
        $andar->descricao = $request->input('descricao');

        $salvo = $predio->andares()->save($andar);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Andar  $andar
     * @return \Illuminate\Http\Response
     */
    public function edit(Andar $andar)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Andar::class);

        return Inertia::render('Cadastro/Andar/Edit', [
            'andar' => fn () => AndarResource::make($andar->load('predio.localidade')),
            'salas' => fn () => SalaCollection::make(
                Pipeline::make()
                    ->send(Sala::withCount(['estantes'])->whereBelongsTo($andar))
                    ->through([SalaOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Andar\UpdateAndarRequest  $request
     * @param  \App\Models\Andar  $andar
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAndarRequest $request, Andar $andar)
    {
        $andar->numero = $request->integer('numero');
        $andar->apelido = $request->input('apelido');
        $andar->descricao = $request->input('descricao');

        $salvo = $andar->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Andar  $andar
     * @return \Illuminate\Http\Response
     */
    public function destroy(Andar $andar)
    {
        $this->authorize(Policy::Delete->value, $andar);

        $excluido = $andar->delete();

        return back()->with($this->feedback($excluido));
    }
}
