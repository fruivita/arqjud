<?php

namespace App\Http\Controllers\Cadastro\Caixa;

use App\Enums\Policy;
use App\Filters\Caixa\JoinLocalidade;
use App\Filters\Caixa\Order;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Resources\Caixa\CaixaCollection;
use App\Models\Caixa;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Inertia\Inertia;

class CaixaController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Caixa::class);

        return Inertia::render('Cadastro/Caixa/Index', [
            'caixas' => CaixaCollection::make(
                app(Pipeline::class)
                    ->send(Caixa::withCount(['volumes'])->with(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora']))
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
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function show(Caixa $caixa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function edit(Caixa $caixa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Caixa $caixa)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Caixa  $caixa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Caixa $caixa)
    {
        $this->authorize(Policy::Delete->value, $caixa);

        $excluido = $caixa->delete();

        return back()->with(...$this->feedback($excluido));
    }
}
