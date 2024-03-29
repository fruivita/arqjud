<?php

namespace App\Http\Controllers\Cadastro\Processo;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Processo\StoreProcessoRequest;
use App\Http\Requests\Cadastro\Processo\UpdateProcessoRequest;
use App\Http\Resources\Caixa\CaixaEditResource;
use App\Http\Resources\Processo\ProcessoCollection;
use App\Http\Resources\Processo\ProcessoEditResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Caixa;
use App\Models\Processo;
use App\Pipes\Processo\JoinLocalidade;
use App\Pipes\Processo\Order;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class ProcessoController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Processo::class);

        return Inertia::render('Cadastro/Processo/Index', [
            'processos' => fn () => ProcessoCollection::make(
                Pipeline::make()
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->with(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora']))
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
    public function create(Caixa $caixa)
    {
        $this->authorize(Policy::Create->value, Processo::class);

        return Inertia::render('Cadastro/Processo/Create', [
            'ultima_insercao' => fn () => ProcessoEditResource::make($caixa->processos()->latest()->first()),
            'caixa' => fn () => CaixaEditResource::make($caixa->load(['prateleira.estante.sala.andar.predio.localidade', 'localidadeCriadora', 'tipoProcesso'])),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreProcessoRequest $request, Caixa $caixa)
    {
        $processo = new Processo();

        $processo->numero = $request->input('numero');
        $processo->numero_antigo = $request->input('numero_antigo');
        $processo->arquivado_em = $request->input('arquivado_em');
        $processo->vol_caixa_inicial = $request->integer('vol_caixa_inicial');
        $processo->vol_caixa_final = $request->integer('vol_caixa_final');
        $processo->qtd_volumes = $request->integer('qtd_volumes');
        $processo->descricao = $request->input('descricao');
        // Assumirá o valor de guarda permanente definido na caixa
        $processo->guarda_permanente = $caixa->guarda_permanente;

        $request->whenFilled(
            'processo_pai_numero',
            fn (string $input) => $processo->processo_pai_id = Processo::where('numero', $input)->firstOrFail()->id,
            fn () => $processo->processo_pai_id = null
        );

        $salvo = $caixa->processos()->save($processo);

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Processo $processo)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Processo::class);

        return Inertia::render('Cadastro/Processo/Edit', [
            'processo' => fn () => ProcessoEditResource::make($processo->load(['caixa.prateleira.estante.sala.andar.predio.localidade', 'caixa.localidadeCriadora', 'caixa.tipoProcesso', 'processoPai'])),
            'processos_filho' => fn () => ProcessoCollection::make(
                Pipeline::make()
                    ->send(Processo::withCount(['processosFilho', 'solicitacoes'])->whereBelongsTo($processo, 'processoPai'))
                    ->through([Order::class])
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
    public function update(UpdateProcessoRequest $request, Processo $processo)
    {
        $processo->numero = $request->input('numero');
        $processo->numero_antigo = $request->input('numero_antigo');
        $processo->arquivado_em = $request->input('arquivado_em');
        $processo->vol_caixa_inicial = $request->integer('vol_caixa_inicial');
        $processo->vol_caixa_final = $request->integer('vol_caixa_final');
        $processo->qtd_volumes = $request->integer('qtd_volumes');
        $processo->descricao = $request->input('descricao');

        $request->whenFilled(
            'processo_pai_numero',
            fn (string $input) => $processo->processo_pai_id = Processo::where('numero', $input)->firstOrFail()->id,
            fn () => $processo->processo_pai_id = null
        );

        $salvo = $processo->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Processo $processo)
    {
        $this->authorize(Policy::Delete->value, $processo);

        $excluido = $processo->delete();

        return back()->with($this->feedback($excluido));
    }
}
