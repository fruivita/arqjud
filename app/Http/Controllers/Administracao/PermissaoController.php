<?php

namespace App\Http\Controllers\Administracao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Administracao\UpdatePermissaoRequest;
use App\Http\Resources\Perfil\PerfilEditResource;
use App\Http\Resources\Permissao\PermissaoCollection;
use App\Http\Resources\Permissao\PermissaoResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Perfil;
use App\Models\Permissao;
use App\Pipes\Perfil\Order as PerfilOrder;
use App\Pipes\Permissao\Order;
use App\Pipes\Search;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class PermissaoController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Permissao::class);

        return Inertia::render('Administracao/Permissao/Index', [
            'permissoes' => fn () => PermissaoCollection::make(
                Pipeline::make()
                    ->send(Permissao::query())
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
     * Show the form for editing the specified resource.
     *
     * @return \Inertia\Response
     */
    public function edit(Permissao $permissao)
    {
        $this->authorize(Policy::ViewOrUpdate->value, Permissao::class);

        return Inertia::render('Administracao/Permissao/Edit', [
            'permissao' => fn () => PermissaoResource::make($permissao),
            'perfis' => fn () => PerfilEditResource::collection(
                Pipeline::make()
                    ->send(Perfil::with(['permissoes' => fn ($query) => $query->select(['id'])->where('permissoes.id', $permissao->id)]))
                    ->through([PerfilOrder::class])
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
    public function update(UpdatePermissaoRequest $request, Permissao $permissao)
    {
        $salvo = $request->whenFilled(
            'perfil_id',
            fn () => $permissao->perfis()->toggle([$request->integer('perfil_id')]),
            function () use ($request, $permissao) {
                $permissao->nome = $request->input('nome');
                $permissao->descricao = $request->input('descricao');

                return $permissao->save();
            }
        );

        return back()->with($this->feedback($salvo));
    }
}
