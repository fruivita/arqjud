<?php

namespace App\Http\Livewire\Traits;

use App\Enums\Feedback;

/**
 * Trait para emitir feedback sobre as ações do usuário.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 * @see https://laravel-livewire.com/docs/2.x/events
 */
trait ComFeedback
{
    /**
     * Emite um evento de feedback para o próprio componente decidir o melhor
     * lugar para exibi-lo.
     *
     * A mensagem informada será enviada ou, se não informada, a mensagem
     * padrão será utilizada.
     *
     * @param bool        $sucesso se o comando foi executado com sucesso
     * @param string|null $mensagem
     *
     * @return void
     */
    private function flashSelf(bool $sucesso, string $mensagem = null)
    {
        if ($sucesso === true) {
            $feedback = Feedback::Sucesso;
            $msg = $mensagem ?? Feedback::Sucesso->nome();
        } else {
            $feedback = Feedback::Erro;
            $msg = $mensagem ?? Feedback::Erro->nome();
        }

        $this->emitSelf('flash', $feedback, $msg);
    }

    /**
     * Dispara um evento de browser para ser captura pelo javascript com os
     * detalhes do resultado do request do usuário.
     *
     * @param bool        $sucesso  se o comando foi executado com sucesso
     * @param string|null $mensagem
     * @param int         $duracao  tempo indicado para duração da mensagem em
     *                              segundos
     *
     * @return void
     */
    private function notificar(bool $sucesso, string $mensagem = null, int $duracao = 3)
    {
        $feedback = ($sucesso === true)
        ? Feedback::Sucesso
        : Feedback::Erro;

        $this->dispatchBrowserEvent('notificacao', [
            'tipo' => $feedback->value,
            'icone' => $feedback->icone(),
            'cabecalho' => $feedback->nome(),
            'mensagem' => $mensagem,
            'duracao' => $duracao * 1000,
        ]);
    }
}
