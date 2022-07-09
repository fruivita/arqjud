<?php

namespace App\Enums;

/*
 * Tipos de feedback dado ao usuário.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://github.com/blade-ui-kit/blade-icons
 */
enum Feedback: string
{
    case Sucesso = 'sucesso';
    case Erro = 'erro';
    /**
     * Nome para exibição do tipo de feedback.
     *
     * @return string
     */
    public function nome()
    {
        return match ($this) {
            Feedback::Erro => __('Erro!'),
            Feedback::Sucesso => __('Sucesso!')
        };
    }

    /**
     * Ícone svg para cada tipo de feedback.
     *
     * @return string
     */
    public function icone()
    {
        return match ($this) {
            Feedback::Erro => svg('emoji-frown')->toHtml(),
            Feedback::Sucesso => svg('emoji-smile')->toHtml(),
        };
    }
}
