<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireCreate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->building = Building::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a floor record without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.create', $this->building->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor record creation route', function () {
    get(route('archiving.register.floor.create', $this->building->id))
    ->assertForbidden();
});

test('cannot render floor record creation component without specific permission', function () {
    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', '')
    ->call('store')
    ->assertHasErrors(['floor.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.number' => 'integer']);
});

test('number must be between -100 and 300', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', -101)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'between'])
    ->set('floor.number', 301)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'between']);
});

test('number and building_id must be unique', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Floor::factory()->create(['number' => 99, 'building_id' => $this->building->id]);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', 99)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'unique']);
});

test('alias is optional', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.alias', '')
    ->call('store')
    ->assertHasNoErrors(['floor.alias']);
});

test('alias must be a string', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.alias', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.alias' => 'string']);
});

test('alias must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.alias', Str::random(101))
    ->call('store')
    ->assertHasErrors(['floor.alias' => 'max']);
});

test('alias and building_id must be unique', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Floor::factory()->create(['alias' => '99', 'building_id' => $this->building->id]);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.alias', '99')
    ->call('store')
    ->assertHasErrors(['floor.alias' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.description', '')
    ->call('store')
    ->assertHasNoErrors(['floor.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['floor.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected floor records', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Floor::factory(30)->for($this->building, 'building')->create();

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('preferencias.por_pagina', 25)
    ->assertCount('floors', 25);
});

test('renders floor record creation component with specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    get(route('archiving.register.floor.create', $this->building->id))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireCreate::class);
});

test('emits feedback event when creates a floor record', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', 1)
    ->set('floor.alias', '1º')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a floor record', function () {
    grantPermission(PermissionType::FloorCreate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->create();

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->call('setToDelete', $floor->id)
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

test('creates a floor record with specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', 99)
    ->set('floor.alias', '99º')
    ->set('floor.description', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $floor = Floor::with('building')->first();

    expect($floor->number)->toBe(99)
    ->and($floor->alias)->toBe('99º')
    ->and($floor->description)->toBe('foo bar')
    ->and($floor->building->id)->toBe($this->building->id);
});

test('reset to a blank model after the floor is created', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $blank = new Floor();

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->set('floor.number', 1)
    ->set('floor.alias', '1º')
    ->call('store')
    ->assertOk()
    ->assertSet('floor', $blank);
});

test('valores iniciais do componente estão definidos', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class, ['id' => $this->building->id])
    ->assertSet('preferencias', [
        'colunas' => [
            'andar',
            'apelido',
            'qtd_salas',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('FloorLivewireCreate uses trait', function () {
    expect(
        collect(class_uses(FloorLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\ConverteStringVaziaEmNull::class,
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
