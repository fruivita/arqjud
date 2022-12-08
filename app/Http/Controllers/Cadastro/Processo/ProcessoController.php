<?php

namespace App\Http\Controllers\Cadastro\Processo;

use App\Enums\Policy;
use App\Filters\Processo\JoinLocalidade;
use App\Filters\Processo\Order;
use App\Filters\Search;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Processo\PostProcessoRequest;
use App\Http\Resources\Processo\ProcessoCollection;
use App\Http\Resources\Processo\ProcessoResource;
use App\Models\Processo;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Inertia\Inertia;

class ProcessoController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Processo::class);

        return Inertia::render('Cadastro/Processo/Index', [
            'processos' => ProcessoCollection::make(
                app(Pipeline::class)
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->with(['volumeCaixa.caixa.prateleira.estante.sala.andar.predio.localidade', 'volumeCaixa.caixa.localidadeCriadora']))
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
     * @param  \App\Models\Processo  $processo
     * @return \Illuminate\Http\Response
     */
    public function show(Processo $processo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Processo  $processo
     * @return \Illuminate\Http\Response
     */
    public function edit(Processo $processo)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Processo::class);

        return Inertia::render('Cadastro/Processo/Edit', [
            'processo' => fn () => ProcessoResource::make($processo->load(['volumeCaixa.caixa.prateleira.estante.sala.andar.predio.localidade', 'volumeCaixa.caixa.localidadeCriadora', 'processoPai'])),
            'processos_filho' => ProcessoCollection::make(
                app(Pipeline::class)
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->whereBelongsTo($processo, 'processoPai'))
                    ->through([Order::class])
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
     * @param  \App\Http\Requests\Cadastro\Processo\PostProcessoRequest  $request
     * @param  \App\Models\Processo  $processo
     * @return \Illuminate\Http\Response
     */
    public function update(PostProcessoRequest $request, Processo $processo)
    {
        $processo->numero = $request->input('numero');
        $processo->numero_antigo = $request->input('numero_antigo');
        $processo->arquivado_em = $request->input('arquivado_em');
        $processo->qtd_volumes = $request->input('qtd_volumes');
        $processo->descricao = $request->input('descricao');

        $request->whenFilled(
            'processo_pai_numero',
            fn ($input) => $processo->processo_pai_id = Processo::firstWhere('numero', $input)->id,
            fn () => $processo->processo_pai_id = null
        );

        $salvo = $processo->save();

        return back()->with(...$this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Processo  $processo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Processo $processo)
    {
        $this->authorize(Policy::Delete->value, $processo);

        $excluido = $processo->delete();

        return back()->with(...$this->feedback($excluido));
    }
}
