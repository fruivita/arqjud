<?php

namespace App\Enums;

/*
 * Permission ids registered in the database.
 *
 * @see https://www.php.net/manual/en/language.enumerations.php
 * @see https://laravel.com/docs/authorization
 */
enum PermissionType: int
{
    case ConfigurationView = 100002;
    case ConfigurationUpdate = 100004;
    case DelegationViewAny = 110001;
    case DelegationCreate = 110003;
    case DocumentationViewAny = 120001;
    case DocumentationCreate = 120003;
    case DocumentationUpdate = 120004;
    case DocumentationDelete = 120006;
    case ImportationCreate = 130003;
    case LogViewAny = 140001;
    case LogDelete = 140006;
    case LogDownload = 140101;
    case PermissionViewAny = 150001;
    case PermissionView = 150002;
    case PermissionUpdate = 150004;
    case RoleViewAny = 160001;
    case RoleView = 160002;
    case RoleUpdate = 160004;
    case SimulationCreate = 170003;
    case UserViewAny = 180001;
    case UserUpdate = 180004;
}
