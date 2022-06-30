<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireCreate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->site = Site::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a building record without being authenticated', function () {
    logout();

    get(route('archiving.register.building.create', $this->site->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building record creation route', function () {
    get(route('archiving.register.building.create', $this->site->id))
    ->assertForbidden();
});

test('cannot render building record creation component without specific permission', function () {
    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->assertForbidden();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', '')
    ->call('store')
    ->assertHasErrors(['building.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', ['foo'])
    ->call('store')
    ->assertHasErrors(['building.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', Str::random(101))
    ->call('store')
    ->assertHasErrors(['building.name' => 'max']);
});

test('name and site_id must be unique', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Building::factory()->create(['name' => 'foo', 'site_id' => $this->site->id]);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', 'foo')
    ->call('store')
    ->assertHasErrors(['building.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.description', '')
    ->call('store')
    ->assertHasNoErrors(['building.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['building.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['building.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Building::factory(30)->for($this->site, 'site')->create();

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('buildings', 25);
});

test('renders building record creation component with specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    get(route('archiving.register.building.create', $this->site->id))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireCreate::class);
});

test('emits feedback event when creates a building record', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', 'name')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
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

test('creates a building record with specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', 'foo')
    ->set('building.description', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $building = Building::with('site')->first();

    expect($building->name)->toBe('foo')
    ->and($building->description)->toBe('foo bar')
    ->and($building->site->id)->toBe($this->site->id);
});

test('reset to a blank model after the building is created', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $blank = new Building();

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->set('building.name', 'foo')
    ->call('store')
    ->assertOk()
    ->assertSet('building', $blank);
});

test('preferencias estão definidas', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['id' => $this->site->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'predio',
            'qtd_andares',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('BuildingLivewireCreate uses trait', function () {
    expect(
        collect(class_uses(BuildingLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
