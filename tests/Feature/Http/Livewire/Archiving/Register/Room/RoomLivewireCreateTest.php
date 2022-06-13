<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireCreate;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Stand;
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
test('cannot create a room record without being authenticated', function () {
    logout();

    get(route('archiving.register.room.create', $this->floor))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access room record creation route', function () {
    get(route('archiving.register.room.create', $this->floor))
    ->assertForbidden();
});

test('cannot render room record creation component without specific permission', function () {
    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->assertForbidden();
});

test('cannot set the room record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->assertOk()
    ->call('markToDelete', $room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the room record which will be deleted if it has stands', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    Stand::factory()
    ->for($room, 'room')
    ->create();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->assertOk()
    ->call('markToDelete', $room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a room record without specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    $component = Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->call('markToDelete', $room->id)
    ->assertOk();

    revokePermission(PermissionType::RoomDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $room->id)->exists())->toBeTrue();
});

test('cannot delete a room record if it has stands', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    $component = Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->call('markToDelete', $room->id)
    ->assertOk();

    Stand::factory()
    ->for($room, 'room')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $room->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('number is required', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', '')
    ->call('store')
    ->assertHasErrors(['room.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['room.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', 0)
    ->call('store')
    ->assertHasErrors(['room.number' => 'between'])
    ->set('room.number', 100001)
    ->call('store')
    ->assertHasErrors(['room.number' => 'between']);
});

test('number and floor_id must be unique', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Room::factory()->create(['number' => 1, 'floor_id' => $this->floor->id]);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', 1)
    ->call('store')
    ->assertHasErrors(['room.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.description', '')
    ->call('store')
    ->assertHasNoErrors(['room.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['room.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['room.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected room records', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Room::factory(120)
    ->for($this->floor, 'floor')
    ->create();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
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
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
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

test('renders room record creation component with specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    get(route('archiving.register.room.create', $this->floor))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireCreate::class);
});

test('emits feedback event when creates a room record', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a room record', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
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

test('creates a room record with specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    expect(Room::count())->toBe(0);

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', 1)
    ->set('room.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $room = Room::with('floor')->first();

    expect($room->number)->toBe(1)
    ->and($room->description)->toBe('foo bar')
    ->and($room->floor->id)->toBe($this->floor->id);
});

test('reset to a blank model after the room is created', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $blank = new Room();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->set('room.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('room', $blank);
});

test('defines the room record that will be deleted with specific permission if it has no stands', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->call('markToDelete', $room->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $room->id);
});

test('delete a room record with specific permission if it has no stands', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireCreate::class, ['floor' => $this->floor])
    ->call('markToDelete', $room->id)
    ->assertOk()
    ->call('destroy', $room->id)
    ->assertOk();

    expect(Room::where('id', $room->id)->doesntExist())->toBeTrue();
});
