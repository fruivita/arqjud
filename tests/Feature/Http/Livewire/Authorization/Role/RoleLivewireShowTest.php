<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->role = Role::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot individually view a role without being authenticated', function () {
    logout();

    get(route('authorization.role.show', $this->role))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual role view route', function () {
    get(route('authorization.role.show', $this->role))
    ->assertForbidden();
});

test('cannot render individual role view component without specific permission', function () {
    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->assertForbidden();
});

// Happy path
test('renders individual role view component with specific permission', function () {
    grantPermission(PermissionType::RoleView->value);

    get(route('authorization.role.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});

test('pagination returns the amount of permissions expected', function () {
    grantPermission(PermissionType::RoleView->value);

    Permission::factory(120)->create();
    $permissions = Permission::all();

    $this->role->permissions()->sync($permissions);

    Livewire::test(RoleLivewireShow::class, ['role' => $this->role])
    ->set('per_page', 25)
    ->assertCount('permissions', 25);
});

test('individually view a role with specific permission', function () {
    grantPermission(PermissionType::RoleView->value);

    get(route('authorization.role.show', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireShow::class);
});
