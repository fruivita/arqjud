<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireIndex;
use App\Models\Building;
use App\Models\Floor;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('cannot list building records without being authenticated', function () {
    logout();

    get(route('archiving.register.building.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building records listing route', function () {
    get(route('archiving.register.building.index'))
    ->assertForbidden();
});

test('cannot render listing component from building records without specific permission', function () {
    Livewire::test(BuildingLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Building::factory(30)->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('buildings', 25);
});

test('lists building records with specific permission', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    get(route('archiving.register.building.index'))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Building::factory()->create(['name' => 'foo']);
    Building::factory()->create(['name' => 'baz']);
    Building::factory()->create(['name' => 'bar']);

    Livewire::test(BuildingLivewireIndex::class)
    ->set('term', 'foo')
    ->assertCount('buildings', 1)
    ->set('term', 'ba')
    ->assertCount('buildings', 2)
    ->set('term', '')
    ->assertCount('buildings', 3);
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->call('setToDelete', $building->id)
    ->call('destroy')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => null,
        'timeout' => 3000,
    ]);
});

test('valores iniciais do componente estão definidos', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Livewire::test(BuildingLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'predio',
            'qtd_andares',
            'localidade',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('BuildingLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(BuildingLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
