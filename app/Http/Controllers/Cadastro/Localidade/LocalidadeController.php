<?php

namespace App\Http\Controllers\Cadastro\Localidade;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastro\Localidade\EditLocalidadeRequest;
use App\Http\Requests\Cadastro\Localidade\PostLocalidadeRequest;
use App\Http\Resources\Localidade\LocalidadeCollection;
use App\Http\Resources\Localidade\LocalidadeResource;
use App\Models\Localidade;
use App\Models\Predio;
use App\Services\Localidade\PesquisarLocalidade;
use App\Services\Predio\PesquisarPredio;
use App\Traits\ComFeedback;
use App\Traits\ComPaginacaoEmCache;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
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
            'localidades' => LocalidadeCollection::make(
                Localidade::query()
                    ->search(request()->query('termo'))
                    ->orderByOrLatest(request()->query('order'))
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
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize(Policy::Create->value, Localidade::class);

        return Inertia::render('Cadastro/Localidade/Create', [
            'ultima_insercao' => fn () => Localidade::select(['nome'])->latest()->first(),
            'links' => fn () => ['create' => route('cadastro.localidade.store')],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Cadastro\Localidade\PostLocalidadeRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PostLocalidadeRequest $request)
    {
        $localidade = new Localidade();

        $localidade->nome = $request->input('nome');
        $localidade->descricao = $request->input('descricao');

        $salvo = $localidade->save();

        return back()->with(...$this->feedback($salvo));
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  \App\Http\Requests\Cadastro\Localidade\EditLocalidadeRequest  $request
    //  * @param  \App\Models\Localidade  $localidade
    //  * @return \Inertia\Response
    //  */
    // public function edit(EditLocalidadeRequest $request, Localidade $localidade)
    // {
    //     return Inertia::render('Cadastro/Localidade/Edit', [
    //         'localidade' => fn () => $localidade,
    //         'can' => fn () => [
    //             'updateLocalidade' => auth()->user()->can(Policy::Update->value, Localidade::class),
    //             'createPredio' => auth()->user()->can(Policy::Create->value, Predio::class),
    //             'viewOrUpdatePredio' => auth()->user()->can(Policy::ViewOrUpdate->value, Predio::class),
    //         ],
    //         'predios' => fn () => PesquisarPredio::make()->pesquisar(
    //             ordenacao: $request->query('order', []),
    //             per_page: intval($request->query('per_page')),
    //             campos: ['id', 'nome', 'andares_count'],
    //             pai: $localidade
    //         ),
    //         'filtros' => fn () => $request->only(['order']),
    //         'per_page' => fn () => $this->perPage(),
    //     ]);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \App\Http\Requests\Cadastro\Localidade\PostLocalidadeRequest  $request
    //  * @param  \App\Models\Localidade  $localidade
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function update(PostLocalidadeRequest $request, Localidade $localidade)
    // {
    //     $localidade->nome = $request->input('nome');
    //     $localidade->descricao = $request->input('descricao');

    //     $salvo = $localidade->save();

    //     return back()->with(...$this->feedback($salvo));
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Localidade  $localidade
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Localidade $localidade)
    {
        $this->authorize(Policy::Delete->value, $localidade);

        $excluido = $localidade->delete();

        return back()->with(...$this->feedback($excluido));
    }
}
