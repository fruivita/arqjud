<?php

namespace App\Http\Controllers\Administracao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administracao\StorePerfilRequest;
use App\Http\Requests\Administracao\UpdatePerfilRequest;
use App\Http\Resources\Perfil\PerfilCollection;
use App\Http\Resources\Perfil\PerfilEditResource;
use App\Http\Resources\Permissao\PermissaoCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Pipes\Perfil\Order;
use App\Pipes\Permissao\Order as PermissaoOrder;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class PerfilController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Perfil::class);

        return Inertia::render('Administracao/Perfil/Index', [
            'perfis' => fn () => PerfilCollection::make(
                Pipeline::make()
                    ->send(Perfil::withCount(['usuarios', 'delegados']))
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
        $this->authorize(Policy::Create->value, Perfil::class);

        return Inertia::render('Administracao/Perfil/Create', [
            'ultima_insercao' => fn () => PerfilEditResource::make(Perfil::latest()->first()),
            'links' => fn () => ['store' => route('administracao.perfil.store')],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Administracao\StorePerfilRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StorePerfilRequest $request)
    {
        $perfil = new Perfil();

        $perfil->nome = $request->input('nome');
        $perfil->slug = $request->input('slug');
        $perfil->poder = $request->integer('poder');
        $perfil->descricao = $request->input('descricao');

        $salvo = $perfil->save();

        return back()->with($this->feedback($salvo));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Perfil  $perfil
     * @return \Inertia\Response
     */
    public function edit(Perfil $perfil)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Perfil::class);

        return Inertia::render('Administracao/Perfil/Edit', [
            'perfil' => fn () => PerfilEditResource::make($perfil),
            'permissoes' => fn () => PermissaoCollection::make(
                Pipeline::make()
                    ->send(Permissao::with(['perfis' => fn ($query) => $query->select(['id'])->where('perfis.id', $perfil->id)]))
                    ->through([PermissaoOrder::class])
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
     * @param  \App\Http\Requests\Administracao\UpdatePerfilRequest  $request
     * @param  \App\Models\Perfil  $perfil
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdatePerfilRequest $request, Perfil $perfil)
    {
        $salvo = $request->whenFilled(
            'permissao_id',
            fn () => $perfil->permissoes()->toggle([$request->integer('permissao_id')]),
            function () use ($request, $perfil) {
                $perfil->nome = $request->input('nome');
                $perfil->slug = $request->input('slug');
                $perfil->descricao = $request->input('descricao');

                return $perfil->save();
            }
        );

        return back()->with($this->feedback($salvo));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Perfil  $perfil
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Perfil $perfil)
    {
        $this->authorize(Policy::Delete->value, $perfil);

        $excluido = $perfil->delete();

        return back()->with($this->feedback($excluido));
    }
}
