<?php

namespace App\Http\Controllers\Atendimento;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Atendimento\StoreDevolverProcessoRequest;
use App\Http\Traits\ComFeedback;
use App\Pipes\Solicitacao\EfetivarDevolucao;
use App\Pipes\Solicitacao\NotificarDevolucao;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class DevolverProcessoController extends Controller
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

        return Inertia::render('Atendimento/DevolverProcesso/Create', [
            'links' => fn () => [
                'devolver' => route('atendimento.devolver-processo.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Atendimento\StoreDevolverProcessoRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreDevolverProcessoRequest $request)
    {
        $devolucao = new \stdClass();
        $devolucao->processo = $request->input('numero');

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($devolucao)
            ->through([
                EfetivarDevolucao::class,
                NotificarDevolucao::class,
            ])->thenReturn();

        return back()->with($this->feedback($salvo));
    }
}
