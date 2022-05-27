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
    // Box
    case BoxViewAny = 100001;
    case BoxView = 100002;
    case BoxCreate = 100003;
    case BoxUpdate = 100004;
    case BoxDelete = 100005;
    case BoxCreateMany = 100101;
    // Box Volumes
    case BoxVolumeViewAny = 110001;
    case BoxVolumeView = 110002;
    case BoxVolumeCreate = 110003;
    case BoxVolumeUpdate = 110004;
    case BoxVolumeDelete = 110005;
    // Configuration
    case ConfigurationView = 120002;
    case ConfigurationUpdate = 120004;
    // Delegation
    case DelegationViewAny = 130001;
    case DelegationCreate = 130003;
    // Documentation
    case DocumentationViewAny = 140001;
    case DocumentationCreate = 140003;
    case DocumentationUpdate = 140004;
    case DocumentationDelete = 140005;
    // Importation
    case ImportationCreate = 150003;
    // Log
    case LogViewAny = 160001;
    case LogDelete = 160005;
    case LogDownload = 160101;
    // Permission
    case PermissionViewAny = 170001;
    case PermissionView = 170002;
    case PermissionUpdate = 170004;
    // Process
    case ProcessViewAny = 180001;
    case ProcessView = 180002;
    case ProcessCreate = 180003;
    case ProcessUpdate = 180004;
    // Role
    case RoleViewAny = 190001;
    case RoleView = 190002;
    case RoleUpdate = 190004;
    // Simulation
    case SimulationCreate = 200003;
    // User
    case UserViewAny = 210001;
    case UserUpdate = 210004;
}
