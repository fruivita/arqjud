<?php

namespace App\Http\Controllers\Administracao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Traits\ComFeedback;
use App\Pipes\Importacao\Importar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class ImportacaoController extends Controller
{
    use ComFeedback;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize(Policy::ImportacaoCreate->value);

        return Inertia::render('Administracao/Importacao/Create', [
            'links' => fn () => [
                'store' => route('administracao.importacao.store'),
            ],
            'opcoes' => [['id' => 'rh', 'nome' => 'Dados do RH']],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->authorize(Policy::ImportacaoCreate->value);

        $importacao = new \stdClass();
        $importacao->importacoes = $request->input('importacoes');

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($importacao)
            ->through([Importar::class])
            ->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha ao executar a importação'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

        $feedback = $salvo
            ? __('Importação foi escalonada para execução. Em breve os dados estarão disponíveis.')
            : null;

        return back()->with($this->feedback($salvo, $feedback));
    }
}
