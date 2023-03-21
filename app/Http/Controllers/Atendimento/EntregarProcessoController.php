<?php

namespace App\Http\Controllers\Atendimento;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Atendimento\StoreEntregarProcessoRequest;
use App\Http\Traits\ComFeedback;
use App\Models\Guia;
use App\Models\Solicitacao;
use App\Pipes\Solicitacao\EfetivarEntrega;
use App\Pipes\Solicitacao\GerarGuiaRemessa;
use App\Pipes\Solicitacao\NotificarEntrega;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class EntregarProcessoController extends Controller
{
    use ComFeedback;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize(Policy::Update->value, Solicitacao::class);

        return Inertia::render('Atendimento/EntregarProcesso/Create', [
            'links' => fn () => [
                'solicitacoes' => route('api.solicitacao.entregas-autorizadas.show'),
                'imprimir_ultima_guia' => function () {
                    $guia = Guia::select('id')->latest()->first();

                    return ($guia)
                        ? route('atendimento.guia.pdf', $guia->id)
                        : null;
                },
                'entregar' => route('atendimento.entregar-processo.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreEntregarProcessoRequest $request)
    {
        $entrega = new \stdClass();
        $entrega->recebedor = $request->input('recebedor');
        $entrega->por_guia = $request->boolean('por_guia');
        $entrega->solicitacoes = $request->input('solicitacoes');

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($entrega)
            ->through([
                GerarGuiaRemessa::class,
                EfetivarEntrega::class,
                NotificarEntrega::class,
            ])->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha entregar o processo'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

        return back()->with($this->feedback($salvo));
    }
}
