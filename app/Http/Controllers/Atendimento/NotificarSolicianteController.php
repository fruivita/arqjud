<?php

namespace App\Http\Controllers\Atendimento;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Atendimento\StoreNotificarSolicitanteRequest;
use App\Http\Traits\ComFeedback;
use App\Models\Solicitacao;
use App\Pipes\Solicitacao\NotificarDisponibilizacao;
use App\Pipes\Solicitacao\RegistrarNotificacao;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use MichaelRubel\EnhancedPipeline\Pipeline;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class NotificarSolicianteController extends Controller
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

        return Inertia::render('Atendimento/NotificarSolicitante/Create', [
            'links' => fn () => [
                'notificar' => route('atendimento.notificar-solicitante.store'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreNotificarSolicitanteRequest $request)
    {
        $notificar = new \stdClass();
        $notificar->processo = $request->input('numero');

        $salvo = Pipeline::make()
            ->withTransaction()
            ->send($notificar)
            ->through([
                RegistrarNotificacao::class,
                NotificarDisponibilizacao::class,
            ])
            ->onFailure(function (mixed $dados, \Throwable $exception) {
                Log::critical(__('Falha ao notificar a disponibilidade do processo solicitado'), [
                    'dados' => $dados,
                    'exception' => $exception,
                ]);

                return false;
            })
            ->then(fn () => true);

        return back()->with($this->feedback($salvo));
    }
}
