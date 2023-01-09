<?php

namespace App\Notifications;

use App\Enums\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

/**
 * @link https://laravel.com/docs/9.x/notifications
 */
class SolicitacaoCancelada extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $detalhes;

    /**
     * Create a new message instance.
     *
     * @param  string  $processo número do processo
     * @param  string  $solicitante
     * @param  string  $destino
     * @param  string  $solicitada_em
     * @param  string  $operador
     * @param  string  $cancelada_em
     * @param  string  $url acesso rápido às solicitações
     * @return void
     */
    public function __construct(
        string $processo,
        string $solicitante,
        string $destino,
        string $solicitada_em,
        string $operador,
        string $cancelada_em,
        string $url
    ) {
        $this->detalhes = Collection::make()
            ->put('processo', $processo)
            ->put('solicitante', $solicitante)
            ->put('destino', $destino)
            ->put('solicitada_em', $solicitada_em)
            ->put('operador', $operador)
            ->put('cancelada_em', $cancelada_em)
            ->put('url', $url);

        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('Solicitação de processo cancelada'))
            ->markdown('emails.solicitacoes.cancelada', ['detalhes' => $this->detalhes]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->detalhes->toArray();
    }

    /**
     * Determine which queues should be used for each notification channel.
     *
     * @return array
     */
    public function viaQueues()
    {
        return [
            'mail' => Queue::Baixa->value,
        ];
    }

    /**
     * Determine if the notification should be sent.
     *
     * @param  mixed  $notifiable
     * @param  string  $channel
     * @return bool
     */
    public function shouldSend($notifiable, $channel)
    {
        return !empty($notifiable->email);
    }
}
