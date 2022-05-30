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

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a room record without being authenticated', function () {
    logout();

    get(route('archiving.register.room.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access room record creation route', function () {
    get(route('archiving.register.room.create'))
    ->assertForbidden();
});

test('cannot render room record creation component without specific permission', function () {
    Livewire::test(RoomLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.number', '')
    ->call('store')
    ->assertHasErrors(['room.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['room.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.number', 0)
    ->call('store')
    ->assertHasErrors(['room.number' => 'between'])
    ->set('room.number', 100001)
    ->call('store')
    ->assertHasErrors(['room.number' => 'between']);
});

test('number and floor_id must be unique', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $floor = Floor::factory()->create();
    Room::factory()->create(['number' => 1, 'floor_id' => $floor->id ]);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.number', 1)
    ->set('floor_id', $floor->id)
    ->call('store')
    ->assertHasErrors(['room.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.description', '')
    ->call('store')
    ->assertHasNoErrors(['room.description']);;
});

test('description must be a string', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['room.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['room.description' => 'max']);
});

test('site_id is optional', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('site_id', '')
    ->call('store')
    ->assertHasNoErrors(['site_id']);;
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('site_id', 'foo')
    ->call('store')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('site_id', 10)
    ->call('store')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $site = Site::factory()->create();

    Livewire::test(RoomLivewireCreate::class)
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is optional', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('building_id', '')
    ->call('store')
    ->assertHasNoErrors(['building_id']);;
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('building_id', 'foo')
    ->call('store')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('building_id', 10)
    ->call('store')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $building = Building::factory()->create();

    Livewire::test(RoomLivewireCreate::class)
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id is required', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('floor_id', '')
    ->call('store')
    ->assertHasErrors(['floor_id' => 'required']);;
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('floor_id', 'foo')
    ->call('store')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Livewire::test(RoomLivewireCreate::class)
    ->set('floor_id', 10)
    ->call('store')
    ->assertHasErrors(['floor_id' => 'exists']);
});

// Happy path
test('renders room record creation component with specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    get(route('archiving.register.room.create'))
    ->assertOk()
    ->assertSeeLivewire(RoomLivewireCreate::class);
});

test('emits feedback event when creates a room record', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $floor = Floor::factory()->create();

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.number', 1)
    ->set('floor_id', $floor->id)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in room creation', function () {
    grantPermission(PermissionType::RoomCreate->value);

    Site::factory(10)->create();

    Livewire::test(RoomLivewireCreate::class)
    ->assertCount('sites', 10);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(RoomLivewireCreate::class)
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('floors are available by selecting a building', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $building = Building::factory()
    ->has(Floor::factory(10), 'floors')
    ->create();

    Livewire::test(RoomLivewireCreate::class)
    ->set('building_id', $building->id)
    ->assertCount('floors', 10);
});

test('creates a room record with specific permission', function () {
    grantPermission(PermissionType::RoomCreate->value);

    $floor = Floor::factory()->create();

    expect(Room::count())->toBe(0);

    Livewire::test(RoomLivewireCreate::class)
    ->set('room.number', 1)
    ->set('room.description', 'foo bar')
    ->set('floor_id', $floor->id)
    ->call('store')
    ->assertOk();

    $room = Room::with('floor')->first();

    expect($room->number)->toBe(1)
    ->and($room->description)->toBe('foo bar')
    ->and($room->floor->id)->toBe($floor->id);
});
