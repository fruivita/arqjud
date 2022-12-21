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
     * @param  \App\Http\Requests\Atendimento\StoreEntregarProcessoRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreEntregarProcessoRequest $request)
    {
        $entrega = new \stdClass();
        $entrega->recebedor = $request->input('recebedor');
        $entrega->por_guia = $request->boolean('por_guia');
        $entrega->solicitacoes = $request->input('solicitacoes');
        $entrega->email_terceiros = $request->input('email_terceiros');

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($entrega)
            ->through([
                GerarGuiaRemessa::class,
                EfetivarEntrega::class,
                NotificarEntrega::class,
            ])->thenReturn();

        return back()->with($this->feedback($salvo));
    }
}
