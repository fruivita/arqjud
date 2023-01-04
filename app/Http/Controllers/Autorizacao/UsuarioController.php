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
use App\Pipes\Usuario\AlterarPerfil;
use App\Pipes\Usuario\JoinAll;
use App\Pipes\Usuario\Order;
use App\Pipes\Usuario\RevogarDelegacoes;
use Illuminate\Support\Facades\Log;
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
                    ->send(Usuario::select('usuarios.*')->with(['lotacao', 'cargo', 'funcaoConfianca', 'perfil', 'perfilAntigo', 'delegante']))
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
     * @param  \App\Models\Usuario  $usuario
     * @return \Inertia\Response
     */
    public function edit(Usuario $usuario)
    {
        $this->authorize(Policy::ViewOrUpdate->value, $usuario);

        return Inertia::render('Autorizacao/Usuario/Edit', [
            'usuario' => fn () => UsuarioResource::make(
                $usuario->load(['lotacao', 'cargo', 'funcaoConfianca', 'perfil', 'perfilAntigo', 'delegante'])
            ),
            'perfis' => fn () => PerfilOnlyResource::collection(Perfil::disponiveisParaAtribuicao()->orderBy('poder', 'desc')->get()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Autorizacao\UpdateUsuarioRequest  $request
     * @param  \App\Models\Usuario  $usuario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUsuarioRequest $request, Usuario $usuario)
    {
        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($usuario)
            ->through([
                AlterarPerfil::class . ':' . $request->integer('perfil_id'),
                RevogarDelegacoes::class,
            ])->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha ao atualizar o usuário'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

        return back()->with($this->feedback($salvo));
    }
}
