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
class ProcessoDisponibilizado extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $detalhes;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        string $processo,
        string $url
    ) {
        $this->detalhes = Collection::make()
            ->put('processo', $processo)
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
            ->subject(__('Processo disponível para retirada no arquivo'))
            ->markdown('emails.solicitacoes.disponibilizada', ['detalhes' => $this->detalhes]);
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
