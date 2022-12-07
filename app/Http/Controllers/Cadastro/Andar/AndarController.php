<?php

namespace App\Http\Controllers\Cadastro\Andar;

use App\Enums\Policy;
use App\Filters\Andar\JoinLocalidade;
use App\Filters\Andar\Order;
use App\Filters\Sala\Order as SalaOrder;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Andar\PostAndarRequest;
use App\Http\Resources\Andar\AndarCollection;
use App\Http\Resources\Andar\AndarResource;
use App\Http\Resources\Sala\SalaCollection;
use App\Models\Andar;
use App\Models\Sala;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Inertia\Inertia;

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
            'andares' => AndarCollection::make(
                app(Pipeline::class)
                    ->send(Andar::withCount(['salas'])->with('predio.localidade'))
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
     * @param  \App\Models\Andar  $andar
     * @return \Illuminate\Http\Response
     */
    public function show(Andar $andar)
    {
        //
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
            'salas' => SalaCollection::make(
                app(Pipeline::class)
                    ->send(Sala::withCount(['estantes'])->whereBelongsTo($andar))
                    ->through([SalaOrder::class])
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
     * @param  \App\Http\Requests\Cadastro\Andar\PostAndarRequest  $request
     * @param  \App\Models\Andar  $andar
     * @return \Illuminate\Http\Response
     */
    public function update(PostAndarRequest $request, Andar $andar)
    {
        $andar->numero = $request->input('numero');
        $andar->apelido = $request->input('apelido');
        $andar->descricao = $request->input('descricao');

        $salvo = $andar->save();

        return back()->with(...$this->feedback($salvo));
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

        return back()->with(...$this->feedback($excluido));
    }
}
