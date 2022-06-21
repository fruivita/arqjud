<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireShow;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->permission = Permission::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot individually view a ticket without being authenticated', function () {
    logout();

    get(route('authorization.permission.show', $this->permission))
    ->assertRedirect(route('login'));
});

test("authenticated but without specific permission, can't access the permission's individual view route", function () {
    get(route('authorization.permission.show', $this->permission))
    ->assertForbidden();
});

test('cannot render individual permission view component without specific permission', function () {
    Livewire::test(PermissionLivewireShow::class, ['permission' => $this->permission])
    ->assertForbidden();
});

// Happy path
test('renders individual permission view component with specific permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    get(route('authorization.permission.show', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireShow::class);
});

test('pagination returns the expected number of roles', function () {
    grantPermission(PermissionType::PermissionView->value);

    Role::factory(30)->create();
    $roles = Role::all();

    $this->permission->roles()->sync($roles);

    Livewire::test(PermissionLivewireShow::class, ['permission' => $this->permission])
    ->set('per_page', 25)
    ->assertCount('roles', 25);
});

test('individually view a permission with specific permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    get(route('authorization.permission.show', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireShow::class);
});
