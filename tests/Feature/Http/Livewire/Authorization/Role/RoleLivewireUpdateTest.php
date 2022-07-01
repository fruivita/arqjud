<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->role = Role::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit role without being authenticated', function () {
    logout();

    get(route('authorization.role.edit', $this->role))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access role edit route', function () {
    get(route('authorization.role.edit', $this->role))
    ->assertForbidden();
});

test('cannot render role editing component without specific permission', function () {
    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->assertForbidden();
});

test('cannot update permission if edit mode is disabled', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('unable to update role without specific permission', function () {
    grantPermission(PermissionType::RoleView->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('role name is required', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.name', '')
    ->call('update')
    ->assertHasErrors(['role.name' => 'required']);
});

test('role name must be a string', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['role.name' => 'string']);
});

test('role name must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['role.name' => 'max']);
});

test('role name must be unique', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $role = Role::factory()->create(['name' => 'another foo']);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->set('modo_edicao', true)
    ->set('role.name', 'foo')
    ->call('update')
    ->assertHasErrors(['role.name' => 'unique']);
});

test('role description is optional', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.description', '')
    ->call('update')
    ->assertHasNoErrors(['role.description']);
});

test('role description must be a string', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.description', ['baz'])
    ->call('update')
    ->assertHasErrors(['role.description' => 'string']);
});

test('role description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['role.description' => 'max']);
});

test('ids of the permissions that will be associated with the role is optional', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('selected', '')
    ->call('update')
    ->assertHasNoErrors(['selected']);
});

test('ids of the permissions that will be associated with the role must be an array', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the permissions that will be associated with the role must previously exist in the database', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('selected', [9090909090])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

// Happy path
test('render role edit component with view or update specific permission', function ($permission) {
    grantPermission($permission);

    get(route('authorization.role.edit', $this->role))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireUpdate::class);
})->with([
    PermissionType::RoleView->value,
    PermissionType::RoleUpdate->value
]);

test('defines the permissions that must be pre-selected according to entity relationships', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Permission::factory(30)->create();
    $role = Role::factory()->has(Permission::factory(20), 'permissions')->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->assertCount('selected', 20);
});

test('permissions checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::RoleUpdate->value);
    $count = Permission::count();

    Permission::factory(50)->create();
    $role = Role::factory()->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('selected', $count + 50)
    ->set('checkbox_action', CheckboxAction::UncheckAll->value)
    ->assertCount('selected', 0)
    ->set('checkbox_action', CheckboxAction::CheckAllPage->value)
    ->assertCount('selected', 10)
    ->set('checkbox_action', CheckboxAction::UncheckAllPage->value)
    ->assertCount('selected', 0);
});

test('pagination returns the amount of permissions expected', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Permission::factory(30)->create();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('permissions', 25);
});

test('getCheckAllProperty displays expected results', function () {
    grantPermission(PermissionType::RoleUpdate->value);
    $count = Permission::count();

    $livewire = Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);

    Permission::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', $count + 3);
});

test('emits feedback event when updating a role', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('associated permissions are optional in role update', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $role = Role::factory()->has(Permission::factory(1), 'permissions')->create();

    expect($role->permissions)->toHaveCount(1);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $role])
    ->set('modo_edicao', true)
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $role->refresh()->load('permissions');

    expect($role->permissions)->toBeEmpty();
});

test('updates a role with specific permission', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    $this->role->load('permissions');

    expect($this->role->name)->toBe('foo')
    ->and($this->role->description)->toBe('bar')
    ->and($this->role->permissions)->toBeEmpty();

    $permission = Permission::first();

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->set('modo_edicao', true)
    ->set('role.name', 'new foo')
    ->set('role.description', 'new bar')
    ->set('selected', [$permission->id])
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->role->refresh();

    expect($this->role->name)->toBe('new foo')
    ->and($this->role->description)->toBe('new bar')
    ->and($this->role->permissions->first()->id)->toBe($permission->id);
});

test('valores iniciais do componente estão definidos', function () {
    grantPermission(PermissionType::RoleUpdate->value);

    Livewire::test(RoleLivewireUpdate::class, ['role' => $this->role])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'seletores',
            'permissao',
            'descricao',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('RoleLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(RoleLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
