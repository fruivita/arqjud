<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireIndex;
use App\Models\Room;
use App\Models\Stand;
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
test('cannot list room records without being authenticated', function () {
    logout();

    get(route('archiving.register.room.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access room records listing route', function () {
    get(route('archiving.register.room.index'))
    ->assertForbidden();
});

test('cannot render listing component from room records without specific permission', function () {
    Livewire::test(RoomLivewireIndex::class)->assertForbidden();
});

// Happy path
test('pagination returns the amount of expected room records', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    Room::factory(30)->create();

    Livewire::test(RoomLivewireIndex::class)
    ->set('preferencias.por_pagina', 25)
    ->assertCount('rooms', 25);
});

test('lists room records with specific permission', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    get(route('archiving.register.room.index'))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    Room::factory()->create(['number' => 10]);
    Room::factory()->create(['number' => 210]);
    Room::factory()->create(['number' => 20]);

    Livewire::test(RoomLivewireIndex::class)
    ->set('term', '210')
    ->assertCount('rooms', 1)
    ->set('term', '10')
    ->assertCount('rooms', 2)
    ->set('term', '')
    ->assertCount('rooms', 3);
});

test('emits feedback event when deleting a room record', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireIndex::class)
    ->call('setToDelete', $room->id)
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
    grantPermission(PermissionType::RoomViewAny->value);

    Livewire::test(RoomLivewireIndex::class)
    ->assertSet('preferencias', [
        'colunas' => [
            'sala',
            'qtd_estantes',
            'localidade',
            'predio',
            'andar',
            'acoes'
        ],
        'por_pagina' => 10
    ]);
});

test('RoomLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(RoomLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\SalvaColunasDePreferencia::class,
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
