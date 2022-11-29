<?php

namespace App\Enums;

/**
 * Tipos de ações/métodos tratados pelas policies.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/9.x/authorization
 */
enum Policy: string
{
    case ViewAny = 'view_any';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';
    case ForceDelete = 'force_delete';
    case ViewAnyOrUpdate = 'view_any_or_update';
    case ViewOrUpdate = 'view_or_update';
    case ExternoViewAny = 'externo_view_any';
    case ExternoCreate = 'externo_create';
    case ExternoDelete = 'externo_delete';
    case DelegacaoViewAny = 'delegacao_view_any';
    case DelegacaoCreate = 'delegacao_create';
    case DelegacaoDelete = 'delegacao_delete';
    case ImportacaoCreate = 'importacao_create';
    case LogViewAny = 'log_view_any';
    case LogView = 'log_view';
    case LogDelete = 'log_delete';
    case MoverProcessoCreate = 'mover_processo_create';
}
