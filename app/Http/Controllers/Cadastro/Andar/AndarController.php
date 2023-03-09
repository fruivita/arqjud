<?php

namespace App\Http\Controllers\Cadastro\Andar;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Andar\StoreAndarRequest;
use App\Http\Requests\Cadastro\Andar\UpdateAndarRequest;
use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Andar\AndarEditResource;
use App\Http\Resources\Predio\PredioEditResource;
use App\Http\Resources\Sala\SalaCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Andar;
use App\Models\Predio;
use App\Models\Sala;
use App\Pipes\Andar\JoinLocalidade;
use App\Pipes\Andar\Order;
use App\Pipes\Sala\Order as SalaOrder;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class AndarController extends Controller
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
     * @return \Inertia\Response
     */
    public function create(Predio $predio)
    {
        $this->authorize(Policy::Create->value, Andar::class);

        return Inertia::render('Cadastro/Andar/Create', [
            'ultima_insercao' => fn () => AndarEditResource::make($predio->andares()->latest()->first()),
            'predio' => fn () => PredioEditResource::make($predio->load('localidade')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
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
     * @return \Inertia\Response
     */
    public function edit(Andar $andar)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Andar::class);

        return Inertia::render('Cadastro/Andar/Edit', [
            'andar' => fn () => AndarEditResource::make($andar->load('predio.localidade')),
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
     * @return \Illuminate\Http\RedirectResponse
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
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Andar $andar)
    {
        $this->authorize(Policy::Delete->value, $andar);

        $excluido = $andar->delete();

        return back()->with($this->feedback($excluido));
    }
}
