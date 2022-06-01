<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Room\RoomLivewireUpdate;
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

    $this->room = Room::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a room record without being authenticated', function () {
    logout();

    get(route('archiving.register.room.edit', $this->room))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access room record edit route', function () {
    get(route('archiving.register.room.edit', $this->room))
    ->assertForbidden();
});

test('cannot render room record edit component without specific permission', function () {
    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.number', '')
    ->call('update')
    ->assertHasErrors(['room.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['room.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.number', 0)
    ->call('update')
    ->assertHasErrors(['room.number' => 'between'])
    ->set('room.number', 100001)
    ->call('update')
    ->assertHasErrors(['room.number' => 'between']);
});

test('number and floor_id must be unique', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $floor = Floor::factory()->create();
    Room::factory()->create(['number' => 1, 'floor_id' => $floor->id]);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.number', 1)
    ->set('room.floor_id', $floor->id)
    ->call('update')
    ->assertHasErrors(['room.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.description', '')
    ->call('update')
    ->assertHasNoErrors(['room.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['room.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['room.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('site_id', 10)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('building_id', '')
    ->call('update')
    ->assertHasErrors(['building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('building_id', 10)
    ->call('update')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id is required', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.floor_id', '')
    ->call('update')
    ->assertHasErrors(['room.floor_id' => 'required']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.floor_id', 'foo')
    ->call('update')
    ->assertHasErrors(['room.floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.floor_id', 10)
    ->call('update')
    ->assertHasErrors(['room.floor_id' => 'exists']);
});

// Happy path
test('renders edit room record component with specific permission', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    get(route('archiving.register.room.edit', $this->room))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireUpdate::class);
});

test('emits feedback event when update a room record', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.number', 1)
    ->set('room.floor_id', $floor->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in room update', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    Site::factory(10)->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->assertCount('sites', 11);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('floors are available by selecting a building', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $building = Building::factory()
    ->has(Floor::factory(10), 'floors')
    ->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('building_id', $building->id)
    ->assertCount('floors', 10);
});

test('update a room record with specific permission', function () {
    grantPermission(PermissionType::RoomUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(RoomLivewireUpdate::class, ['room' => $this->room])
    ->set('room.number', 1)
    ->set('room.description', 'foo bar')
    ->set('room.floor_id', $floor->id)
    ->call('update')
    ->assertOk();

    $this->room->refresh()->load('floor');

    expect($this->room->number)->toBe(1)
    ->and($this->room->description)->toBe('foo bar')
    ->and($this->room->floor->id)->toBe($floor->id);
});
