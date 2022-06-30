<?php

namespace Database\Seeders;

use App\Enums\PermissionType;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class PermissionRoleSeeder extends Seeder
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

        DB::table('permission_role')->insert(
            $this
            ->allRolesPermissions()
            ->map(function ($item) use ($now) {
                $item['created_at'] = $now;
                $item['updated_at'] = $now;

                return $item;
            })
            ->toArray()
        );
    }

    /**
     * All roles and their respective permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function allRolesPermissions()
    {
        return $this->administratorPermissions()
        ->concat($this->businessManagerPermissions())
        ->concat($this->observerPermissions())
        ->concat($this->ordinaryPermissions());
    }

    /**
     * Initial administrator role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function administratorPermissions()
    {
        return LazyCollection::make([
            PermissionType::BoxViewAny->value,
            PermissionType::BoxView->value,
            PermissionType::BoxCreate->value,
            PermissionType::BoxCreateMany->value,
            PermissionType::BoxUpdate->value,
            PermissionType::BoxDelete->value,
            PermissionType::BoxVolumeViewAny->value,
            PermissionType::BoxVolumeView->value,
            PermissionType::BoxVolumeCreate->value,
            PermissionType::BoxVolumeUpdate->value,
            PermissionType::BoxVolumeDelete->value,
            PermissionType::BuildingViewAny->value,
            PermissionType::BuildingView->value,
            PermissionType::BuildingCreate->value,
            PermissionType::BuildingUpdate->value,
            PermissionType::BuildingDelete->value,
            PermissionType::ConfigurationView->value,
            PermissionType::ConfigurationUpdate->value,
            PermissionType::DelegationViewAny->value,
            PermissionType::DelegationCreate->value,
            PermissionType::DocumentationViewAny->value,
            PermissionType::DocumentationView->value,
            PermissionType::DocumentationCreate->value,
            PermissionType::DocumentationUpdate->value,
            PermissionType::DocumentationDelete->value,
            PermissionType::FloorViewAny->value,
            PermissionType::FloorView->value,
            PermissionType::FloorCreate->value,
            PermissionType::FloorUpdate->value,
            PermissionType::FloorDelete->value,
            PermissionType::ImportationCreate->value,
            PermissionType::LogViewAny->value,
            PermissionType::LogDelete->value,
            PermissionType::LogDownload->value,
            PermissionType::PermissionViewAny->value,
            PermissionType::PermissionView->value,
            PermissionType::PermissionUpdate->value,
            PermissionType::RoleViewAny->value,
            PermissionType::RoleView->value,
            PermissionType::RoleUpdate->value,
            PermissionType::RoomViewAny->value,
            PermissionType::RoomView->value,
            PermissionType::RoomCreate->value,
            PermissionType::RoomUpdate->value,
            PermissionType::RoomDelete->value,
            PermissionType::SimulationCreate->value,
            PermissionType::ShelfViewAny->value,
            PermissionType::ShelfView->value,
            PermissionType::ShelfCreate->value,
            PermissionType::ShelfUpdate->value,
            PermissionType::ShelfDelete->value,
            PermissionType::SiteViewAny->value,
            PermissionType::SiteView->value,
            PermissionType::SiteCreate->value,
            PermissionType::SiteUpdate->value,
            PermissionType::SiteDelete->value,
            PermissionType::StandViewAny->value,
            PermissionType::StandView->value,
            PermissionType::StandCreate->value,
            PermissionType::StandUpdate->value,
            PermissionType::StandDelete->value,
            PermissionType::UserViewAny->value,
            PermissionType::UserUpdate->value,
        ])->map(function ($item) {
            $new_item['role_id'] = Role::ADMINISTRATOR;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }

    /**
     * Initial institutional manager role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function businessManagerPermissions()
    {
        return LazyCollection::make([
            // ...
        ])->map(function ($item) {
            $new_item['role_id'] = Role::BUSINESSMANAGER;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }

    /**
     * Initial department manager role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function observerPermissions()
    {
        return LazyCollection::make([
            // ...
        ])->map(function ($item) {
            $new_item['role_id'] = Role::OBSERVER;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }

    /**
     * Initial ordinary role permissions.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    private function ordinaryPermissions()
    {
        return LazyCollection::make([
            // ...
        ])->map(function ($item) {
            $new_item['role_id'] = Role::ORDINARY;
            $new_item['permission_id'] = $item;

            return $new_item;
        });
    }
}
