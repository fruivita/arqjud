<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireIndex;
use App\Models\Box;
use App\Models\Room;
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

test('cannot set the room record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Room());
});

test('cannot set the room record which will be deleted it it has boxes', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()
    ->has(Box::factory(2), 'boxes')
    ->create();

    Livewire::test(RoomLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Room());
});

test('cannot delete a room record without specific permission', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    $room = Room::factory()->create(['number' => 20]);

    Livewire::test(RoomLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $room->id)
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('number', 20)->exists())->toBeTrue();
});

test('cannot delete a room record it it has boxes', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    $component = Livewire::test(RoomLivewireIndex::class)
    ->call('markToDelete', $room->id)
    ->assertOk();

    $boxes = Box::factory(2)->make();

    $room->boxes()->saveMany($boxes);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $room->id)->get())->toHaveCount(1);
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    Livewire::test(RoomLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of expected room records', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    Room::factory(120)->create();

    Livewire::test(RoomLivewireIndex::class)
    ->assertCount('rooms', 10)
    ->set('per_page', 10)
    ->assertCount('rooms', 10)
    ->set('per_page', 25)
    ->assertCount('rooms', 25)
    ->set('per_page', 50)
    ->assertCount('rooms', 50)
    ->set('per_page', 100)
    ->assertCount('rooms', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    Livewire::test(RoomLivewireIndex::class)
    ->assertSessionMissing('per_page')
    ->set('per_page', 10)
    ->assertSessionHas('per_page', 10)
    ->set('per_page', 25)
    ->assertSessionHas('per_page', 25)
    ->set('per_page', 50)
    ->assertSessionHas('per_page', 50)
    ->set('per_page', 100)
    ->assertSessionHas('per_page', 100);
});

test('lists room records with specific permission', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    get(route('archiving.register.room.index'))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireIndex::class);
});

test('emits feedback event when deleting a room record', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create(['number' => 20]);

    Livewire::test(RoomLivewireIndex::class)
    ->call('markToDelete', $room->id)
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

test('defines the room record that will be deleted with specific permission if it has no boxes', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create(['number' => 20]);

    Livewire::test(RoomLivewireIndex::class)
    ->call('markToDelete', $room->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $room->id);
});

test('delete a room record with specific permission if it has no boxes', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create(['number' => 20]);

    expect(Room::where('number', 20)->exists())->toBeTrue();

    Livewire::test(RoomLivewireIndex::class)
    ->call('markToDelete', $room->id)
    ->assertOk()
    ->call('destroy', $room->id)
    ->assertOk();

    expect(Room::where('number', 20)->doesntExist())->toBeTrue();
});
