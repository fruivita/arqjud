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
class ProcessoEntregue extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $detalhes;

    /**
     * @var array
     */
    public $email_terceiros;

    /**
     * Create a new message instance.
     *
     * @param  string  $guia_numero número da guia de remessa
     * @param  array  $processos número e quantidade de volume dos processos
     * @param  string[]  $email_terceiros
     * @return void
     */
    public function __construct(
        string $guia_numero,
        array $processos,
        string $recebedor,
        string $destino,
        string $entregue_em,
        bool $por_guia,
        string $url,
        array $email_terceiros = []
    ) {
        $this->detalhes = Collection::make()
            ->put('guia_numero', $guia_numero)
            ->put('processos', $processos)
            ->put('recebedor', $recebedor)
            ->put('destino', $destino)
            ->put('entregue_em', $entregue_em)
            ->put('por_guia', $por_guia)
            ->put('url', $url);

        $this->email_terceiros = $email_terceiros;

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
            ->when(!empty($this->email_terceiros), fn (MailMessage $mail) => $mail->bcc($this->email_terceiros))
            ->subject(__('Processos solicitados entregues'))
            ->markdown('emails.solicitacoes.entregue', ['detalhes' => $this->detalhes]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->detalhes->toArray() + ['email_terceiros' => $this->email_terceiros];
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
