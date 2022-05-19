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
        ->concat($this->configurationPermissions())
        ->concat($this->delegationPermissions())
        ->concat($this->documentationPermissions())
        ->concat($this->importationPermissions())
        ->concat($this->logPermissions())
        ->concat($this->permissionPermissions())
        ->concat($this->rolePermissions())
        ->concat($this->simulationPermissions())
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
