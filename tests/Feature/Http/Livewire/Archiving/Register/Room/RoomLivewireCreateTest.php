<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireCreate;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Shelf;
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

    get(route('archiving.register.room.create', $this->floor->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access room record creation route', function () {
    get(route('archiving.register.room.create', $this->floor->id))
    ->assertForbidden();
});

test('cannot render room record creation component without specific permission', function () {
    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', '')
    ->call('store')
    ->assertHasErrors(['room.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['room.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', 0)
    ->call('store')
    ->assertHasErrors(['room.number' => 'between'])
    ->set('room.number', 100001)
    ->call('store')
    ->assertHasErrors(['room.number' => 'between']);
});

test('number and floor_id must be unique', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Room::factory()->create(['number' => 99, 'floor_id' => $this->floor->id]);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', 99)
    ->call('store')
    ->assertHasErrors(['room.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.description', '')
    ->call('store')
    ->assertHasNoErrors(['room.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['room.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['room.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected room records', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Room::factory(30)->for($this->floor, 'floor')->create();

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('per_page', 25)
    ->assertCount('rooms', 25);
});

test('renders room record creation component with specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    get(route('archiving.register.room.create', $this->floor->id))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireCreate::class);
});

test('emits feedback event when creates a room record', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', 1)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a room record', function () {
    grantPermission(PermissionType::RoomCreate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $room = Room::factory()->create();

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
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

test('creates a room record with specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', 99)
    ->set('room.description', 'foo bar')
    ->call('store')
    ->assertHasNoErrors()
    ->assertOk();

    $room = Room::with('floor')->first();

    expect($room->number)->toBe('99')
    ->and($room->description)->toBe('foo bar')
    ->and($room->floor->id)->toBe($this->floor->id);
});

test('when creating a room, a default stand and shelf are also created', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', 99)
    ->set('room.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $room = Room::with('stands.shelves')->first();
    $stand = $room->stands()->first();
    $shelf = $stand->shelves()->first();

    expect($room->number)->toBe('99')
    ->and($room->description)->toBe('foo bar')
    ->and($room->floor_id)->toBe($this->floor->id)
    ->and($stand->number)->toBe(0)
    ->and($stand->alias)->toBe(__('Uninformed'))
    ->and($stand->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'))
    ->and($stand->room_id)->toBe($room->id)
    ->and($shelf->number)->toBe(0)
    ->and($shelf->alias)->toBe(__('Uninformed'))
    ->and($shelf->stand_id)->toBe($stand->id)
    ->and($shelf->description)->toBe(__('Provisional/default item created by the system for possible future analysis. If it is not a mandatory attribute, it can be ignored'));
});

test('reset to a blank model after the room is created', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $blank = new Room();

    Livewire::test(RoomLivewireCreate::class, ['id' => $this->floor->id])
    ->set('room.number', 1)
    ->call('store')
    ->assertOk()
    ->assertSet('room', $blank);
});

test('RoomLivewireCreate uses trait', function () {
    expect(
        collect(class_uses(RoomLivewireCreate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
