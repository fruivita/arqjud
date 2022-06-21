<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireIndex;
use App\Models\Permission;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list permissions without being authenticated', function () {
    logout();

    get(route('authorization.permission.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access permissions listing route', function () {
    get(route('authorization.permission.index'))
    ->assertForbidden();
});

test('cannot render permissions listing component without specific permission', function () {
    Livewire::test(PermissionLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of permissions expected', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    Permission::factory(30)->create();

    Livewire::test(PermissionLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('permissions', 25);
});

test('list permissions with specific permission', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    get(route('authorization.permission.index'))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireIndex::class);
});
