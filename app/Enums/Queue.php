<?php

namespace App\Enums;

/**
 * Listas de queues utilizadas na aplicação de acordo com o grau sugerido de
 * prioridade no processamento.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/9.x/authorization
 */
enum Queue: string
{
    case Imediata = 'imediata';
    case Alta = 'alta';
    case Media = 'media';
    case Baixa = 'baixa';
}
