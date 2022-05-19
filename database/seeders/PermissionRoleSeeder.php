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
            PermissionType::BoxUpdate->value,
            PermissionType::BoxDelete->value,
            PermissionType::ConfigurationView->value,
            PermissionType::ConfigurationUpdate->value,
            PermissionType::DelegationViewAny->value,
            PermissionType::DelegationCreate->value,
            PermissionType::DocumentationViewAny->value,
            PermissionType::DocumentationCreate->value,
            PermissionType::DocumentationUpdate->value,
            PermissionType::DocumentationDelete->value,
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
            PermissionType::SimulationCreate->value,
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
