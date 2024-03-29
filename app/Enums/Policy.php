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
    case ViewAny = 'viewAny';
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
    case Restore = 'restore';
    case ForceDelete = 'forceDelete';
    case ViewAnyOrUpdate = 'viewAnyOrUpdate';
    case ViewOrUpdate = 'viewOrUpdate';
    case ExternoViewAny = 'externoViewAny';
    case ExternoCreate = 'externoCreate';
    case ExternoDelete = 'externoDelete';
    case ImportacaoCreate = 'importacaoCreate';
    case LogViewAny = 'logViewAny';
    case LogView = 'logView';
    case MoverProcessoCreate = 'moverProcessoCreate';
}
