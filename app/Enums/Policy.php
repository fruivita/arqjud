<?php

namespace App\Enums;

/*
 * Tipos de ações/métodos tratados pelas policies.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/authorization
 */
enum Policy: string
{
    case ViewAny = 'view-any';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';
    case ForceDelete = 'force-delete';
    case CreateMany = 'create-many';
    case DelegacaoViewAny = 'delegacao-view-any';
    case DelegacaoCreate = 'delegacao-create';
    case DelegacaoDelete = 'delegacao-delete';
    case ImportacaoCreate = 'importacao-create';
    case LogViewAny = 'log-view-any';
    case LogDownload = 'log-download';
    case LogDelete = 'log-delete';
    case SimulacaoCreate = 'simulacao-create';
    case SimulacaoDelete = 'simulacao-delete';
    case ViewOrUpdate = 'view-or-update';
    case ViewAnyOrUpdate = 'view-any-or-update';
}
