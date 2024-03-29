<?php

namespace App\Listeners;

use App\Enums\Queue;
use App\Models\Solicitacao;
use App\Models\Usuario;
use FruiVita\Corporativo\Events\CargoUsuarioAlterado;
use FruiVita\Corporativo\Events\FuncaoConfiancaUsuarioAlterada;
use FruiVita\Corporativo\Events\ImportacaoConcluida;
use FruiVita\Corporativo\Events\ImportacaoIniciada;
use FruiVita\Corporativo\Events\LotacaoUsuarioAlterada;
use Illuminate\Contracts\Queue\ShouldQueue;

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
     * @return void
     */
    public function handleImportacaoIniciada(ImportacaoIniciada $event)
    {
        activity(__('Importação RH'))
            ->event('job')
            ->withProperties(['arquivo' => $event->arquivo])
            ->log(__('iniciado'));
    }

    /**
     * Handle ImportacaoConcluida events.
     *
     * @return void
     */
    public function handleImportacaoConcluida(ImportacaoConcluida $event)
    {
        activity(__('Importação RH'))
            ->event('job')
            ->withProperties(['arquivo' => $event->arquivo])
            ->log(__('concluído'));
    }

    /**
     * Handle CargoUsuarioAlterado events.
     *
     * @return void
     */
    public function handleCargoUsuarioAlterado(CargoUsuarioAlterado $event)
    {
        Usuario::resetarPerfil($event->usuario);
    }

    /**
     * Handle FuncaoConfiancaUsuarioAlterada events.
     *
     * @return void
     */
    public function handleFuncaoConfiancaUsuarioAlterada(FuncaoConfiancaUsuarioAlterada $event)
    {
        Usuario::resetarPerfil($event->usuario);
    }

    /**
     * Handle LotacaoUsuarioAlterada events.
     *
     * @return void
     */
    public function handleLotacaoUsuarioAlterada(LotacaoUsuarioAlterada $event)
    {
        Usuario::resetarPerfil($event->usuario);

        Solicitacao::query()
            ->solicitadas()
            ->where('solicitante_id', $event->usuario)
            ->each(fn (Solicitacao $solicitacao) => $solicitacao->delete());
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
