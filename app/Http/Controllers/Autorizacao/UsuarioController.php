<?php

namespace App\Http\Controllers\Autorizacao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Autorizacao\UpdateUsuarioRequest;
use App\Http\Resources\Perfil\PerfilOnlyResource;
use App\Http\Resources\Usuario\UsuarioCollection;
use App\Http\Resources\Usuario\UsuarioResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Perfil;
use App\Models\Usuario;
use App\Pipes\Search;
use App\Pipes\Usuario\JoinAll;
use App\Pipes\Usuario\Order;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class UsuarioController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Usuario::class);

        return Inertia::render('Autorizacao/Usuario/Index', [
            'usuarios' => fn () => UsuarioCollection::make(
                Pipeline::make()
                    ->send(Usuario::select('usuarios.*')->with(['lotacao', 'cargo', 'funcaoConfianca', 'perfil']))
                    ->through([JoinAll::class, Order::class, Search::class])
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
    public function edit(Usuario $usuario)
    {
        $this->authorize(Policy::ViewOrUpdate->value, $usuario);

        return Inertia::render('Autorizacao/Usuario/Edit', [
            'usuario' => fn () => UsuarioResource::make(
                $usuario->load(['lotacao', 'cargo', 'funcaoConfianca', 'perfil'])
            ),
            'perfis' => fn () => PerfilOnlyResource::collection(Perfil::disponiveisParaAtribuicao()->orderBy('poder', 'desc')->get()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $salvo = $usuario
            ->perfil()
            ->associate($request->integer('perfil_id'))
            ->save();

        return back()->with($this->feedback($salvo));
    }
}
