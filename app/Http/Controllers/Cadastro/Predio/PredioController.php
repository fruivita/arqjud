<?php

namespace App\Http\Controllers\Cadastro\Predio;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Predio\StorePredioRequest;
use App\Http\Requests\Cadastro\Predio\UpdatePredioRequest;
use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Http\Resources\Predio\PredioCollection;
use App\Http\Resources\Predio\PredioEditResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Pipes\Andar\Order as AndarOrder;
use App\Pipes\Predio\JoinLocalidade;
use App\Pipes\Predio\Order;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
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
     * @return \Inertia\Response
     */
    public function create(Localidade $localidade)
    {
        $this->authorize(Policy::Create->value, Predio::class);

        return Inertia::render('Cadastro/Predio/Create', [
            'ultima_insercao' => fn () => PredioEditResource::make($localidade->predios()->latest()->first()),
            'localidade' => fn () => LocalidadeEditResource::make($localidade),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
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
     * @return \Inertia\Response
     */
    public function edit(Predio $predio)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Predio::class);

        return Inertia::render('Cadastro/Predio/Edit', [
            'predio' => fn () => PredioEditResource::make($predio->load('localidade')),
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
     * @return \Illuminate\Http\RedirectResponse
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Predio $predio)
    {
        $this->authorize(Policy::Delete->value, $predio);

        $excluido = $predio->delete();

        return back()->with($this->feedback($excluido));
    }
}
