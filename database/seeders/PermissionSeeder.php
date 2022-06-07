<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('permissions')->insert(
            $this->allPermissions()
            ->map(function ($item) use ($now) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;

                return $item;
            })
            ->toArray()
        );
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function allPermissions()
    {
        return $this->boxPermissions()
        ->concat($this->boxVolumePermissions())
        ->concat($this->buildingPermissions())
        ->concat($this->configurationPermissions())
        ->concat($this->delegationPermissions())
        ->concat($this->documentationPermissions())
        ->concat($this->floorPermissions())
        ->concat($this->importationPermissions())
        ->concat($this->logPermissions())
        ->concat($this->permissionPermissions())
        ->concat($this->rolePermissions())
        ->concat($this->roomPermissions())
        ->concat($this->simulationPermissions())
        ->concat($this->shelfPermissions())
        ->concat($this->sitePermissions())
        ->concat($this->standPermissions())
        ->concat($this->userPermissions());
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function boxPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::BoxViewAny->value,
                'name' => __('Box: View all'),
                'description' => __('Permission to view all registered boxes.'),
            ],
            [
                'id' => PermissionType::BoxView->value,
                'name' => __('Box: View one'),
                'description' => __('Permission to individually view registered boxes.'),
            ],
            [
                'id' => PermissionType::BoxCreate->value,
                'name' => __('Box: Create one'),
                'description' => __('Permission to individually create boxes.'),
            ],
            [
                'id' => PermissionType::BoxCreateMany->value,
                'name' => __('Box: Create many'),
                'description' => __('Permission to create many boxes at once.'),
            ],
            [
                'id' => PermissionType::BoxUpdate->value,
                'name' => __('Box: Update one'),
                'description' => __('Permission to individually update registered boxes.'),
            ],
            [
                'id' => PermissionType::BoxDelete->value,
                'name' => __('Box: Delete one'),
                'description' => __('Permission to individually delete registered boxes.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function boxVolumePermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::BoxVolumeViewAny->value,
                'name' => __('Box volume: View all'),
                'description' => __('Permission to view all registered box volumes.'),
            ],
            [
                'id' => PermissionType::BoxVolumeView->value,
                'name' => __('Box volume: View one'),
                'description' => __('Permission to individually view registered box volumes.'),
            ],
            [
                'id' => PermissionType::BoxVolumeCreate->value,
                'name' => __('Box volume: Create'),
                'description' => __('Permission to create box volumes.'),
            ],
            [
                'id' => PermissionType::BoxVolumeUpdate->value,
                'name' => __('Box volume: Update one'),
                'description' => __('Permission to individually update registered box volumes.'),
            ],
            [
                'id' => PermissionType::BoxVolumeDelete->value,
                'name' => __('Box volume: Delete one'),
                'description' => __('Permission to individually delete registered box volumes.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function buildingPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::BuildingViewAny->value,
                'name' => __('Building: View all'),
                'description' => __('Permission to view all registered buildings.'),
            ],
            [
                'id' => PermissionType::BuildingView->value,
                'name' => __('Building: View one'),
                'description' => __('Permission to individually view registered buildings.'),
            ],
            [
                'id' => PermissionType::BuildingCreate->value,
                'name' => __('Building: Create one'),
                'description' => __('Permission to individually create buildings.'),
            ],
            [
                'id' => PermissionType::BuildingUpdate->value,
                'name' => __('Building: Update one'),
                'description' => __('Permission to individually update registered buildings.'),
            ],
            [
                'id' => PermissionType::BuildingDelete->value,
                'name' => __('Building: Delete one'),
                'description' => __('Permission to individually delete registered buildings.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function configurationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::ConfigurationView->value,
                'name' => __('Application settings: View'),
                'description' => __('Permission to view registered application settings.'),
            ],
            [
                'id' => PermissionType::ConfigurationUpdate->value,
                'name' => __('Application settings: Update'),
                'description' => __('Permission to update registered application settings.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function documentationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::DocumentationViewAny->value,
                'name' => __('Documentation: View all'),
                'description' => __('Permission to view all registered application documentation.'),
            ],
            [
                'id' => PermissionType::DocumentationCreate->value,
                'name' => __('Documentation: Create one'),
                'description' => __('Permission to individually create application documentation.'),
            ],
            [
                'id' => PermissionType::DocumentationUpdate->value,
                'name' => __('Documentation: Update one'),
                'description' => __('Permission to individually update registered application documentation.'),
            ],
            [
                'id' => PermissionType::DocumentationDelete->value,
                'name' => __('Documentation: Delete one'),
                'description' => __('Permission to individually delete registered application documentation.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function delegationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::DelegationViewAny->value,
                'name' => __('Delegation: View all'),
                'description' => __('Permission to view all department delegations.'),
            ],
            [
                'id' => PermissionType::DelegationCreate->value,
                'name' => __('Delegation: Create'),
                'description' => __('Permission to delegate the role (and its permissions) to another user in the same department.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function floorPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::FloorViewAny->value,
                'name' => __('Floor: View all'),
                'description' => __('Permission to view all registered floors.'),
            ],
            [
                'id' => PermissionType::FloorView->value,
                'name' => __('Floor: View one'),
                'description' => __('Permission to individually view registered floors.'),
            ],
            [
                'id' => PermissionType::FloorCreate->value,
                'name' => __('Floor: Create one'),
                'description' => __('Permission to individually create floors.'),
            ],
            [
                'id' => PermissionType::FloorUpdate->value,
                'name' => __('Floor: Update one'),
                'description' => __('Permission to individually update registered floors.'),
            ],
            [
                'id' => PermissionType::FloorDelete->value,
                'name' => __('Floor: Delete one'),
                'description' => __('Permission to individually delete registered floors.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function importationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::ImportationCreate->value,
                'name' => __('Importation: Create'),
                'description' => __('Permission to request forced data import.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function logPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::LogViewAny->value,
                'name' => __('Log: View all'),
                'description' => __('Permission to view all application log files.'),
            ],
            [
                'id' => PermissionType::LogDelete->value,
                'name' => __('Log: Delete one'),
                'description' => __('Permission to individually delete application log files.'),
            ],
            [
                'id' => PermissionType::LogDownload->value,
                'name' => __('Log: Download one'),
                'description' => __('Permission to individually download application log files.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function permissionPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::PermissionViewAny->value,
                'name' => __('Permission: View all'),
                'description' => __('Permission to view all registered permissions.'),
            ],
            [
                'id' => PermissionType::PermissionView->value,
                'name' => __('Permission: View one'),
                'description' => __('Permission to individually view registered permissions.'),
            ],
            [
                'id' => PermissionType::PermissionUpdate->value,
                'name' => __('Permission: Update one'),
                'description' => __('Permission to individually update registered permissions.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function rolePermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::RoleViewAny->value,
                'name' => __('Role: View all'),
                'description' => __('Permission to view all registered roles.'),
            ],
            [
                'id' => PermissionType::RoleView->value,
                'name' => __('Role: View one'),
                'description' => __('Permission to individually view registered roles.'),
            ],
            [
                'id' => PermissionType::RoleUpdate->value,
                'name' => __('Role: Update one'),
                'description' => __('Permission to individually update registered roles.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function roomPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::RoomViewAny->value,
                'name' => __('Room: View all'),
                'description' => __('Permission to view all registered rooms.'),
            ],
            [
                'id' => PermissionType::RoomView->value,
                'name' => __('Room: View one'),
                'description' => __('Permission to individually view registered rooms.'),
            ],
            [
                'id' => PermissionType::RoomCreate->value,
                'name' => __('Room: Create one'),
                'description' => __('Permission to individually create rooms.'),
            ],
            [
                'id' => PermissionType::RoomUpdate->value,
                'name' => __('Room: Update one'),
                'description' => __('Permission to individually update registered rooms.'),
            ],
            [
                'id' => PermissionType::RoomDelete->value,
                'name' => __('Room: Delete one'),
                'description' => __('Permission to individually delete registered rooms.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function simulationPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::SimulationCreate->value,
                'name' => __('Simulation: Create'),
                'description' => __('Permission to simulate using the application as if it were another user.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function shelfPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::ShelfViewAny->value,
                'name' => __('Shelf: View all'),
                'description' => __('Permission to view all registered shelves.'),
            ],
            [
                'id' => PermissionType::ShelfView->value,
                'name' => __('Shelf: View one'),
                'description' => __('Permission to individually view registered shelves.'),
            ],
            [
                'id' => PermissionType::ShelfCreate->value,
                'name' => __('Shelf: Create one'),
                'description' => __('Permission to individually create shelves.'),
            ],
            [
                'id' => PermissionType::ShelfUpdate->value,
                'name' => __('Shelf: Update one'),
                'description' => __('Permission to individually update registered shelves.'),
            ],
            [
                'id' => PermissionType::ShelfDelete->value,
                'name' => __('Shelf: Delete one'),
                'description' => __('Permission to individually delete registered shelves.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function sitePermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::SiteViewAny->value,
                'name' => __('Site: View all'),
                'description' => __('Permission to view all registered sites.'),
            ],
            [
                'id' => PermissionType::SiteView->value,
                'name' => __('Site: View one'),
                'description' => __('Permission to individually view registered sites.'),
            ],
            [
                'id' => PermissionType::SiteCreate->value,
                'name' => __('Site: Create one'),
                'description' => __('Permission to individually create sites.'),
            ],
            [
                'id' => PermissionType::SiteUpdate->value,
                'name' => __('Site: Update one'),
                'description' => __('Permission to individually update registered sites.'),
            ],
            [
                'id' => PermissionType::SiteDelete->value,
                'name' => __('Site: Delete one'),
                'description' => __('Permission to individually delete registered sites.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function standPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::StandViewAny->value,
                'name' => __('Stand: View all'),
                'description' => __('Permission to view all registered stands.'),
            ],
            [
                'id' => PermissionType::StandView->value,
                'name' => __('Stand: View one'),
                'description' => __('Permission to individually view registered stands.'),
            ],
            [
                'id' => PermissionType::StandCreate->value,
                'name' => __('Stand: Create one'),
                'description' => __('Permission to individually create stands.'),
            ],
            [
                'id' => PermissionType::StandUpdate->value,
                'name' => __('Stand: Update one'),
                'description' => __('Permission to individually update registered stands.'),
            ],
            [
                'id' => PermissionType::StandDelete->value,
                'name' => __('Stand: Delete one'),
                'description' => __('Permission to individually delete registered stands.'),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Support\LazyCollection
     */
    private function userPermissions()
    {
        return LazyCollection::make([
            [
                'id' => PermissionType::UserViewAny->value,
                'name' => __('User: View all'),
                'description' => __('Permission to view all registered users.'),
            ],
            [
                'id' => PermissionType::UserUpdate->value,
                'name' => __('User: Update one'),
                'description' => __('Permission to individually update registered users.'),
            ],
        ]);
    }
}
