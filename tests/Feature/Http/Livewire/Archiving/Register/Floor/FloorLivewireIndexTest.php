<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireIndex;
use App\Models\Floor;
use App\Models\Room;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->floor = Floor::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot list floor records without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor records listing route', function () {
    get(route('archiving.register.floor.index'))
    ->assertForbidden();
});

test('cannot render listing component from floor records without specific permission', function () {
    Livewire::test(FloorLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of expected floor records', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    Floor::factory(30)->create();

    Livewire::test(FloorLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('floors', 25);
});

test('lists floor records with specific permission', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    get(route('archiving.register.floor.index'))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    $this->floor->delete();

    Floor::factory()->create(['number' => 10, 'alias' => 'foo']);
    Floor::factory()->create(['number' => 210, 'alias' => 'bar']);
    Floor::factory()->create(['number' => 20, 'alias' => 'baz']);

    Livewire::test(FloorLivewireIndex::class)
    ->set('term', '210')
    ->assertCount('floors', 1)
    ->set('term', '10')
    ->assertCount('floors', 2)
    ->set('term', '')
    ->assertCount('floors', 3)
    ->set('term', 'ba')
    ->assertCount('floors', 2);
});

test('emits feedback event when deleting a floor record', function () {
    grantPermission(PermissionType::FloorViewAny->value);
    grantPermission(PermissionType::FloorDelete->value);

    Livewire::test(FloorLivewireIndex::class)
    ->call('setToDelete', $this->floor->id)
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

test('colunas ocultáveis estão pré-definidas', function () {
    grantPermission(PermissionType::FloorViewAny->value);

    Livewire::test(FloorLivewireIndex::class)
    ->assertSet('colunas', [
        'andar',
        'apelido',
        'qtd_salas',
        'localidade',
        'predio',
        'acoes'
    ]);
});

test('FloorLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(FloorLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
