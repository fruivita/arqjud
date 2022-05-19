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
    case BoxViewAny = 100001;
    case BoxView = 100002;
    case BoxCreate = 100003;
    case BoxUpdate = 100004;
    case BoxDelete = 100005;
    case ConfigurationView = 110002;
    case ConfigurationUpdate = 110004;
    case DelegationViewAny = 120001;
    case DelegationCreate = 120003;
    case DocumentationViewAny = 130001;
    case DocumentationCreate = 130003;
    case DocumentationUpdate = 130004;
    case DocumentationDelete = 130005;
    case ImportationCreate = 140003;
    case LogViewAny = 150001;
    case LogDelete = 150005;
    case LogDownload = 150101;
    case PermissionViewAny = 160001;
    case PermissionView = 160002;
    case PermissionUpdate = 160004;
    case ProcessViewAny = 170001;
    case ProcessView = 170002;
    case ProcessCreate = 170003;
    case ProcessUpdate = 170004;
    case RoleViewAny = 180001;
    case RoleView = 180002;
    case RoleUpdate = 180004;
    case SimulationCreate = 190003;
    case UserViewAny = 200001;
    case UserUpdate = 200004;
}
