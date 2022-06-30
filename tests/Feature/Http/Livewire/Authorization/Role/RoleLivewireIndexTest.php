<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Authorization\Role\RoleLivewireIndex;
use App\Models\Role;
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
test('cannot list roles without being authenticated', function () {
    logout();

    get(route('authorization.role.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access role listing route', function () {
    get(route('authorization.role.index'))
    ->assertForbidden();
});

test('cannot render role listing component without specific permission', function () {
    Livewire::test(RoleLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the expected number of roles', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Role::factory(30)->create();

    Livewire::test(RoleLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('roles', 25);
});

test('lists roles with specific permission', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    get(route('authorization.role.index'))
    ->assertOk()
    ->assertSeeLivewire(RoleLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Role::factory()->create(['name' => 'role_foo']);
    Role::factory()->create(['name' => 'role_bar']);
    Role::factory()->create(['name' => 'role_baz']);

    Livewire::test(RoleLivewireIndex::class)
    ->set('term', 'foo')
    ->assertCount('roles', 1)
    ->set('term', 'ba')
    ->assertCount('roles', 2)
    ->set('term', '')
    ->assertCount('roles', Role::count());
});

test('valores iniciais do componente estão definidos', function () {
    grantPermission(PermissionType::RoleViewAny->value);

    Livewire::test(RoleLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'perfil',
            'permissoes',
            'acoes',
        ],
        'por_pagina' => 10
    ]);
});

test('RoleLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(RoleLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithLimit::class,
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
