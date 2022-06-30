<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\CheckboxAction;
use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Permission\PermissionLivewireUpdate;
use App\Models\Permission;
use App\Models\Role;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->permission = Permission::factory()->create(['name' => 'foo', 'description' => 'bar']);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot edit permission without being authenticated', function () {
    logout();

    get(route('authorization.permission.edit', $this->permission))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access permission edit route', function () {
    get(route('authorization.permission.edit', $this->permission))
    ->assertForbidden();
});

test('cannot render permission edit component without specific permission', function () {
    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->assertForbidden();
});

test('cannot update permission if edit mode is disabled', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('cannot update permission without specific permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('permission name is required', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.name', '')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'required']);
});

test('permission name must be a string', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.name', ['bar'])
    ->call('update')
    ->assertHasErrors(['permission.name' => 'string']);
});

test('permission name must be a maximum of 50 characters', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.name', Str::random(51))
    ->call('update')
    ->assertHasErrors(['permission.name' => 'max']);
});

test('permission name must be unique', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission = Permission::factory()->create(['name' => 'another foo']);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('modo_edicao', true)
    ->set('permission.name', 'foo')
    ->call('update')
    ->assertHasErrors(['permission.name' => 'unique']);
});

test('permission description is optional', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.description', '')
    ->call('update')
    ->assertHasNoErrors(['permission.description']);
});

test('permission description must be a string', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.description', ['baz'])
    ->call('update')
    ->assertHasErrors(['permission.description' => 'string']);
});

test('permission description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['permission.description' => 'max']);
});

test('ids of the roles that will be associated with the permission is optional', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('selected', '')
    ->call('update')
    ->assertHasNoErrors(['selected']);
});

test('ids of the roles that will be associated with the permission must be an array', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('selected', 1)
    ->call('update')
    ->assertHasErrors(['selected' => 'array']);
});

test('ids of the roles that will be associated with the permission must previously exist in the database', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('selected', [9090909090])
    ->call('update')
    ->assertHasErrors(['selected' => 'exists']);
});

// Happy path
test('renders permission edit component with view or update permission', function ($permission) {
    grantPermission($permission);

    get(route('authorization.permission.edit', $this->permission))
    ->assertOk()
    ->assertSeeLivewire(PermissionLivewireUpdate::class);
})->with([
    PermissionType::PermissionView->value,
    PermissionType::PermissionUpdate->value
]);

test('define the roles that should be pre-selected according to the permission relationships', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Role::factory(30)->create();
    $permission = Permission::factory()->has(Role::factory(20), 'roles')->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->assertCount('selected', 20);
});

test('roles checkbox manipulation actions work as expected', function () {
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    Role::factory(50)->create();
    $permission = Permission::factory()->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
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

test('pagination returns the expected number of roles', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Role::factory(30)->create();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('roles', 25);
});

test('getCheckAllProperty displays expected results', function () {
    grantPermission(PermissionType::PermissionUpdate->value);
    $count = Role::count();

    $livewire = Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('checkbox_action', CheckboxAction::CheckAll->value);

    Role::factory(3)->create();

    $livewire
    ->set('checkbox_action', CheckboxAction::CheckAll->value)
    ->assertCount('CheckAll', $count + 3);
});

test('emits feedback event when updating a permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('associated roles are optional in the permission update', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $permission = Permission::factory()->has(Role::factory(1), 'roles')->create();

    expect($permission->roles)->toHaveCount(1);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $permission])
    ->set('modo_edicao', true)
    ->set('selected', null)
    ->call('update')
    ->assertOk();

    $permission->refresh()->load('roles');

    expect($permission->roles)->toBeEmpty();
});

test('updates a permission with specific permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    $this->permission->load('roles');

    expect($this->permission->name)->toBe('foo')
    ->and($this->permission->description)->toBe('bar')
    ->and($this->permission->roles)->toBeEmpty();

    $role = Role::first();

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->set('modo_edicao', true)
    ->set('permission.name', 'new foo')
    ->set('permission.description', 'new bar')
    ->set('selected', [$role->id])
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->permission->refresh();

    expect($this->permission->name)->toBe('new foo')
    ->and($this->permission->description)->toBe('new bar')
    ->and($this->permission->roles->first()->id)->toBe($role->id);
});

test('valores iniciais do componente estão definidos', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    Livewire::test(PermissionLivewireUpdate::class, ['permission' => $this->permission])
    ->assertSet('modo_edicao', false)
    ->assertSet('preferencias', [
        'colunas' => [
            'nome',
            'descricao',
            'selecao',
        ],
        'por_pagina' => 10
    ]);
});

test('PermissionLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(PermissionLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
