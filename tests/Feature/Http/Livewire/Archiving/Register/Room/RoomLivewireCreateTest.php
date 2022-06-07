<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireCreate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
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

// Rules
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
