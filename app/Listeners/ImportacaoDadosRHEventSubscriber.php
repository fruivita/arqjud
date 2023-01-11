<?php

namespace App\Listeners;

use App\Enums\Queue;
use App\Models\Usuario;
use FruiVita\Corporativo\Events\CargoUsuarioAlterado;
use FruiVita\Corporativo\Events\FuncaoConfiancaUsuarioAlterada;
use FruiVita\Corporativo\Events\ImportacaoConcluida;
use FruiVita\Corporativo\Events\ImportacaoIniciada;
use FruiVita\Corporativo\Events\LotacaoUsuarioAlterada;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * @link https://laravel.com/docs/9.x/events
 */
class ImportacaoDadosRHEventSubscriber implements ShouldQueue
{
    /**
     * Número de tentativas de execução do job.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Número de segundos de espera antes de tentar executar novamente o job.
     *
     * @var int[]
     */
    public $backoff = [60, 300];

    /**
     * Handle ImportacaoIniciada events.
     *
     * @param  \FruiVita\Corporativo\Events\ImportacaoIniciada  $event
     * @return void
     */
    public function handleImportacaoIniciada(ImportacaoIniciada $event)
    {
        Log::notice(
            __('Importação dos dados corporativos iniciada'),
            [
                'iniciado_em' => $event->iniciado_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'arquivo' => $event->arquivo,
            ]
        );
    }

    /**
     * Handle ImportacaoConcluida events.
     *
     * @param  \FruiVita\Corporativo\Events\ImportacaoConcluida  $event
     * @return void
     */
    public function handleImportacaoConcluida(ImportacaoConcluida $event)
    {
        Log::notice(
            __('Importação dos dados corporativos concluída'),
            [
                'concluido_em' => $event->concluido_em->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                'arquivo' => $event->arquivo,
            ]
        );
    }

    /**
     * Handle CargoUsuarioAlterado events.
     *
     * @param  \FruiVita\Corporativo\Events\CargoUsuarioAlterado  $event
     * @return void
     */
    public function handleCargoUsuarioAlterado(CargoUsuarioAlterado $event)
    {
        Usuario::resetarPerfil($event->usuario);
    }

    /**
     * Handle FuncaoConfiancaUsuarioAlterada events.
     *
     * @param  \FruiVita\Corporativo\Events\FuncaoConfiancaUsuarioAlterada  $event
     * @return void
     */
    public function handleFuncaoConfiancaUsuarioAlterada(FuncaoConfiancaUsuarioAlterada $event)
    {
        Usuario::resetarPerfil($event->usuario);
    }

    /**
     * Handle LotacaoUsuarioAlterada events.
     *
     * @param  \FruiVita\Corporativo\Events\LotacaoUsuarioAlterada  $event
     * @return void
     */
    public function handleLotacaoUsuarioAlterada(LotacaoUsuarioAlterada $event)
    {
        Usuario::resetarPerfil($event->usuario);
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
            ImportacaoIniciada::class => 'handleImportacaoIniciada',
            ImportacaoConcluida::class => 'handleImportacaoConcluida',
            CargoUsuarioAlterado::class => 'handleCargoUsuarioAlterado',
            FuncaoConfiancaUsuarioAlterada::class => 'handleFuncaoConfiancaUsuarioAlterada',
            LotacaoUsuarioAlterada::class => 'handleLotacaoUsuarioAlterada',
        ];
    }

    /**
     * Get the name of the listener's queue.
     *
     * @return string
     */
    public function viaQueue()
    {
        return Queue::Baixa->value;
    }
}
