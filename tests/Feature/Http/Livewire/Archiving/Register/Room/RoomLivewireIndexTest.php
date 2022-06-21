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

    $this->room = Room::factory()->create();

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

    Livewire::test(RoomLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the room record which will be deleted if it has stands', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    Stand::factory()->for($this->room, 'room')->create();

    Livewire::test(RoomLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a room record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $component = Livewire::test(RoomLivewireIndex::class)
    ->call('setToDelete', $this->room->id)
    ->assertOk();

    revokePermission(PermissionType::RoomDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $this->room->id)->exists())->toBeTrue();
});

test('cannot delete a room record if it has stands', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    $component = Livewire::test(RoomLivewireIndex::class)
    ->call('setToDelete', $this->room->id)
    ->assertOk();

    Stand::factory()->for($this->room, 'room')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $this->room->id)->exists())->toBeTrue();
});

// Happy path
test('pagination returns the amount of expected room records', function () {
    grantPermission(PermissionType::RoomViewAny->value);

    Room::factory(30)->create();

    Livewire::test(RoomLivewireIndex::class)
    ->set('per_page', 25)
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

    $this->room->delete();

    Room::factory()->create(['number' => 10]);
    Room::factory()->create(['number' => 210]); // contains 10
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

    Livewire::test(RoomLivewireIndex::class)
    ->call('setToDelete', $this->room->id)
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

test('defines the room record that will be deleted with specific permission if it has no stands', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    Livewire::test(RoomLivewireIndex::class)
    ->call('setToDelete', $this->room->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->room->id);
});

test('delete a room record with specific permission if it has no stands', function () {
    grantPermission(PermissionType::RoomViewAny->value);
    grantPermission(PermissionType::RoomDelete->value);

    expect(Room::where('id', $this->room->id)->exists())->toBeTrue();

    Livewire::test(RoomLivewireIndex::class)
    ->call('setToDelete', $this->room->id)
    ->assertOk()
    ->call('destroy', $this->room->id)
    ->assertOk();

    expect(Room::where('id', $this->room->id)->doesntExist())->toBeTrue();
});

test('RoomLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(RoomLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
