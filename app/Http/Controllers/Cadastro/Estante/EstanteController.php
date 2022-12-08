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
use App\Models\Estante;
use App\Models\Prateleira;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use MichaelRubel\EnhancedPipeline\Pipeline;
use Inertia\Inertia;

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
