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
    // Building
    case BuildingViewAny = 120001;
    case BuildingView = 120002;
    case BuildingCreate = 120003;
    case BuildingUpdate = 120004;
    case BuildingDelete = 120005;
    // Configuration
    case ConfigurationView = 130002;
    case ConfigurationUpdate = 130004;
    // Delegation
    case DelegationViewAny = 140001;
    case DelegationCreate = 140003;
    // Documentation
    case DocumentationViewAny = 150001;
    case DocumentationView = 150002;
    case DocumentationCreate = 150003;
    case DocumentationUpdate = 150004;
    case DocumentationDelete = 150005;
    // Floor
    case FloorViewAny = 160001;
    case FloorView = 160002;
    case FloorCreate = 160003;
    case FloorUpdate = 160004;
    case FloorDelete = 160005;
    // Importation
    case ImportationCreate = 170003;
    // Log
    case LogViewAny = 180001;
    case LogDelete = 180005;
    case LogDownload = 180101;
    // Permission
    case PermissionViewAny = 190001;
    case PermissionView = 190002;
    case PermissionUpdate = 190004;
    // Process
    case ProcessViewAny = 200001;
    case ProcessView = 200002;
    case ProcessCreate = 200003;
    case ProcessUpdate = 200004;
    // Role
    case RoleViewAny = 210001;
    case RoleView = 210002;
    case RoleUpdate = 210004;
    // Room
    case RoomViewAny = 220001;
    case RoomView = 220002;
    case RoomCreate = 220003;
    case RoomUpdate = 220004;
    case RoomDelete = 220005;
    // Simulation
    case SimulationCreate = 230003;
    // Shelf
    case ShelfViewAny = 240001;
    case ShelfView = 240002;
    case ShelfCreate = 240003;
    case ShelfUpdate = 240004;
    case ShelfDelete = 240005;
    // Site
    case SiteViewAny = 250001;
    case SiteView = 250002;
    case SiteCreate = 250003;
    case SiteUpdate = 250004;
    case SiteDelete = 250005;
    // Stand
    case StandViewAny = 260001;
    case StandView = 260002;
    case StandCreate = 260003;
    case StandUpdate = 260004;
    case StandDelete = 260005;
    // User
    case UserViewAny = 270001;
    case UserUpdate = 270004;
}
