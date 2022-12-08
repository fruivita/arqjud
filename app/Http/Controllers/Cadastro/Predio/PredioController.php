<?php

namespace App\Http\Controllers\Cadastro\Predio;

use App\Enums\Policy;
use App\Filters\Andar\Order as AndarOrder;
use App\Filters\Predio\JoinLocalidade;
use App\Filters\Predio\Order;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Predio\PostPredioRequest;
use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Localidade\LocalidadeOnlyResource;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Http\Resources\Predio\PredioCollection;
use App\Http\Resources\Predio\PredioOnlyResource;
use App\Http\Resources\Predio\PredioResource;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use MichaelRubel\EnhancedPipeline\Pipeline;
use Inertia\Inertia;

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
            'predios' => PredioCollection::make(
                Pipeline::make()
                    ->send(Predio::withCount(['andares'])->with('localidade'))
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
     * @param  \App\Http\Requests\Cadastro\Predio\PostPredioRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PostPredioRequest $request)
    {
        $predio = new Predio();

        $predio->nome = $request->input('nome');
        $predio->descricao = $request->input('descricao');
        $predio->localidade_id = $request->input('localidade_id');

        $salvo = $predio->save();

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
            'andares' => AndarCollection::make(
                Pipeline::make()
                    ->send(Andar::withCount(['salas'])->whereBelongsTo($predio))
                    ->through([AndarOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Predio\PostPredioRequest  $request
     * @param  \App\Models\Predio  $predio
     * @return \Illuminate\Http\Response
     */
    public function update(PostPredioRequest $request, Predio $predio)
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
