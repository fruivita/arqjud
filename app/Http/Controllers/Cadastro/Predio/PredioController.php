<?php

namespace App\Http\Controllers\Cadastro\Predio;

use App\Enums\Policy;
use App\Filters\Andar\Order as AndarOrder;
use App\Filters\Predio\JoinLocalidade;
use App\Filters\Predio\Order;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Predio\StorePredioRequest;
use App\Http\Requests\Cadastro\Predio\UpdatePredioRequest;
use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Http\Resources\Predio\PredioCollection;
use App\Http\Resources\Predio\PredioResource;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

class PredioController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Predio::class);

        return Inertia::render('Cadastro/Predio/Index', [
            'predios' => fn () => PredioCollection::make(
                Pipeline::make()
                    ->send(Predio::withCount(['andares'])->with('localidade'))
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
     * @param  \App\Models\Localidade  $localidade
     * @return \Inertia\Response
     */
    public function create(Localidade $localidade)
    {
        $this->authorize(Policy::Create->value, Predio::class);

        return Inertia::render('Cadastro/Predio/Create', [
            'ultima_insercao' => fn () => PredioResource::make($localidade->predios()->latest()->first()),
            'localidade' => fn () => LocalidadeResource::make($localidade),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Predio\StorePredioRequest  $request
     * @param  \App\Models\Localidade  $localidade
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePredioRequest $request, Localidade $localidade)
    {
        $predio = new Predio();

        $predio->nome = $request->input('nome');
        $predio->descricao = $request->input('descricao');

        $salvo = $localidade->predios()->save($predio);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Predio  $predio
     * @return \Illuminate\Http\Response
     */
    public function edit(Predio $predio)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Predio::class);

        return Inertia::render('Cadastro/Predio/Edit', [
            'predio' => fn () => PredioResource::make($predio->load('localidade')),
            'andares' => fn () => AndarCollection::make(
                Pipeline::make()
                    ->send(Andar::withCount(['salas'])->whereBelongsTo($predio))
                    ->through([AndarOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Predio\UpdatePredioRequest  $request
     * @param  \App\Models\Predio  $predio
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePredioRequest $request, Predio $predio)
    {
        $predio->nome = $request->input('nome');
        $predio->descricao = $request->input('descricao');

        $salvo = $predio->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Predio  $predio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Predio $predio)
    {
        $this->authorize(Policy::Delete->value, $predio);

        $excluido = $predio->delete();

        return back()->with($this->feedback($excluido));
    }
}
