<?php

namespace App\Traits;

/**
 * Mensagem de feedback ao usuário.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComFeedback
{
    /**
     * Gera o feedback de acordo com o resultado da operação realizada pelo
     * usuário.
     *
     * Caso a mensagem seja informada, ela será usada.
     *
     * @param  mixed  $resultado
     * @param  string|null  $mensagem
     * @return array<string, array<string, string>>
     */
    public function feedback(mixed $resultado, string $mensagem = null)
    {
        return [
            'feedback' => $resultado
                ? ['sucesso' => $mensagem ?? __('Comando executado com sucesso!')]
                : ['erro' => $mensagem ?? __('Falha na execução do comando!')],
        ];
    }
}
