<?php

namespace App\Listeners;

use App\Events\ProcessoSolicitadoPeloUsuario;
use App\Models\Usuario;
use App\Notifications\ProcessoSolicitado as NotificacaoProcessoSolicitado;
use Illuminate\Support\Facades\Notification;

/**
 * @link https://laravel.com/docs/9.x/events
 */
class SolicitacaoSubscriber
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\ProcessoSolicitadoPeloUsuario  $event
     * @return void
     */
    public function handleProcessoSolicitadoPeloUsuario(ProcessoSolicitadoPeloUsuario $event)
    {
        $event->solicitante->loadMissing('lotacao');

        Notification::send(
            Usuario::operadores()->get(),
            new NotificacaoProcessoSolicitado(
                $event->processos,
                $event->solicitante->nome ?? $event->solicitante->username,
                $event->solicitante->lotacao->nome ?? $event->solicitante->lotacao->sigla,
                $event->solicitada_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                // @todo add rota de visualização das solicitações
                'rota'
            )
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return array
     */
    public function subscribe($events)
    {
        return [
            ProcessoSolicitadoPeloUsuario::class => 'handleProcessoSolicitadoPeloUsuario',
        ];
    }
}
