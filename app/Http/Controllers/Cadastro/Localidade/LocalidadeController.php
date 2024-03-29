<?php

namespace App\Http\Controllers\Cadastro\Localidade;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Localidade\StoreLocalidadeRequest;
use App\Http\Requests\Cadastro\Localidade\UpdateLocalidadeRequest;
use App\Http\Resources\Localidade\LocalidadeCollection;
use App\Http\Resources\Localidade\LocalidadeEditResource;
use App\Http\Resources\Predio\PredioCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Localidade;
use App\Models\Predio;
use App\Pipes\Localidade\Order;
use App\Pipes\Predio\Order as PredioOrder;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class LocalidadeController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Localidade::class);

        return Inertia::render('Cadastro/Localidade/Index', [
            'localidades' => fn () => LocalidadeCollection::make(
                Pipeline::make()
                    ->send(Localidade::withCount(['predios', 'caixasCriadas']))
                    ->through([Order::class, Search::class])
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
    public function create()
    {
        $this->authorize(Policy::Create->value, Localidade::class);

        return Inertia::render('Cadastro/Localidade/Create', [
            'ultima_insercao' => fn () => LocalidadeEditResource::make(Localidade::latest()->first()),
            'links' => fn () => ['store' => route('cadastro.localidade.store')],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreLocalidadeRequest $request)
    {
        $localidade = new Localidade();

        $localidade->nome = $request->input('nome');
        $localidade->descricao = $request->input('descricao');

        $salvo = $localidade->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Localidade $localidade)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Localidade::class);

        return Inertia::render('Cadastro/Localidade/Edit', [
            'localidade' => fn () => LocalidadeEditResource::make($localidade),
            'predios' => fn () => PredioCollection::make(
                Pipeline::make()
                    ->send(Predio::withCount(['andares'])->whereBelongsTo($localidade))
                    ->through([PredioOrder::class])
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
    public function update(UpdateLocalidadeRequest $request, Localidade $localidade)
    {
        $localidade->nome = $request->input('nome');
        $localidade->descricao = $request->input('descricao');

        $salvo = $localidade->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Localidade $localidade)
    {
        $this->authorize(Policy::Delete->value, $localidade);

        $excluido = $localidade->delete();

        return back()->with($this->feedback($excluido));
    }
}
