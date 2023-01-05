<?php

namespace App\Http\Controllers\Administracao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Resources\Lotacao\LotacaoCollection;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use App\Models\Lotacao;
use App\Pipes\Lotacao\JoinAll;
use App\Pipes\Lotacao\Order;
use App\Pipes\Lotacao\ResetarPerfis;
use App\Pipes\Lotacao\ToggleAdministravel;
use App\Pipes\Search;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/9.x/controllers
 * @see https://laravel.com/docs/9.x/requests
 * @see https://laravel.com/docs/9.x/responses
 * @see https://inertiajs.com/
 */
class LotacaoController extends Controller
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
        $this->authorize(Policy::ViewAny->value, Lotacao::class);

        return Inertia::render('Administracao/Lotacao/Index', [
            'lotacoes' => fn () => LotacaoCollection::make(
                Pipeline::make()
                    ->send(Lotacao::withCount('usuarios')->with('lotacaoPai'))
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
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Lotacao  $lotacao
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Lotacao $lotacao)
    {
        $this->authorize(Policy::Update->value, Lotacao::class);

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($lotacao)
            ->through([
                ToggleAdministravel::class,
                ResetarPerfis::class,
            ])->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha ao alterar a administrabilidade da lotação'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

        return back()->with($this->feedback($salvo));
    }
}
