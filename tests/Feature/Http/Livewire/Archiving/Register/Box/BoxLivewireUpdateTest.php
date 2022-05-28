<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireUpdate;
use App\Models\Box;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->box = Box::factory()->create(['year' => 2020, 'number' => 100]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a box record without being authenticated', function () {
    logout();

    get(route('archiving.register.box.edit', $this->box))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access box edit route', function () {
    get(route('archiving.register.box.edit', $this->box))
    ->assertForbidden();
});

test('cannot render edit box record component without specific permission', function () {
    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertForbidden();
});

// Rules
test('site_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('site_id', 10)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('building_id', 10)
    ->call('update')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('floor_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('floor_id', 10)
    ->call('update')
    ->assertHasErrors(['floor_id' => 'exists']);
});

test('floor_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('floor_id', $floor->id)
    ->assertHasNoErrors()
    ->set('floor_id', 'foo')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('box.room_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.room_id', '')
    ->call('update')
    ->assertHasErrors(['box.room_id' => 'required']);
});

test('box.room_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.room_id', 'foo')
    ->call('update')
    ->assertHasErrors(['box.room_id' => 'integer']);
});

test('box.room_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.room_id', 10)
    ->call('update')
    ->assertHasErrors(['box.room_id' => 'exists']);
});

test('box.room_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.room_id', $room->id)
    ->assertHasNoErrors()
    ->set('box.room_id', 'foo')
    ->assertHasErrors(['box.room_id' => 'integer']);
});

test('box.year is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', '')
    ->call('update')
    ->assertHasErrors(['box.year' => 'required']);
});

test('box.year must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 'foo')
    ->call('update')
    ->assertHasErrors(['box.year' => 'integer']);
});

test('box.year must be between 1900 and the current year', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 1899)
    ->call('update')
    ->assertHasErrors(['box.year' => 'between'])
    ->set('box.year', now()->addYear()->format('Y'))
    ->call('update')
    ->assertHasErrors(['box.year' => 'between']);
});

test('box.number is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.number', '')
    ->call('update')
    ->assertHasErrors(['box.number' => 'required']);
});

test('box.number must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.number', 'foo')
    ->call('update')
    ->assertHasErrors(['box.number' => 'integer']);
});

test('box.number must be greater then 1', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.number', 0)
    ->call('update')
    ->assertHasErrors(['box.number' => 'min']);
});

test('box.number and year must be unique', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Box::factory()->create(['year' => 2020, 'number' => 10]);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 2020)
    ->set('box.number', 10)
    ->call('update')
    ->assertHasErrors(['box.number' => 'unique']);
});

test('box.stand must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.stand', 'foo')
    ->call('update')
    ->assertHasErrors(['box.stand' => 'integer']);
});

test('box.stand must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.stand', 0)
    ->call('update')
    ->assertHasErrors(['box.stand' => 'between'])
    ->set('box.stand', 1001)
    ->call('update')
    ->assertHasErrors(['box.stand' => 'between']);
});

test('box.shelf must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.shelf', 'foo')
    ->call('update')
    ->assertHasErrors(['box.shelf' => 'integer']);
});

test('box.shelf must be between 1 and 1000', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.shelf', 0)
    ->call('update')
    ->assertHasErrors(['box.shelf' => 'between'])
    ->set('box.shelf', 1001)
    ->call('update')
    ->assertHasErrors(['box.shelf' => 'between']);
});

// Happy path
test('renders edit box record component with specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    get(route('archiving.register.box.edit', $this->box))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireUpdate::class);
});

test('emits feedback event when update a box record', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 2000)
    ->set('box.number', 10)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('box.room_id', $room->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in box update', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Site::factory(10)->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertCount('sites', 11);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('floors are available by selecting a building', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $building = Building::factory()
    ->has(Floor::factory(10), 'floors')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('building_id', $building->id)
    ->assertCount('floors', 10);
});

test('rooms are available by selecting a floor', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $floor = Floor::factory()
    ->has(Room::factory(10), 'rooms')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('floor_id', $floor->id)
    ->assertCount('rooms', 10);
});

test('sites, buildings, floors and rooms are pre-selected according to the edit box', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $this->box->load('room.floor.building.site');

    Room::factory(4)
    ->for($this->box->room->floor)
    ->create();

    Floor::factory(8)
    ->for($this->box->room->floor->building)
    ->create();

    Building::factory(2)
    ->for($this->box->room->floor->building->site)
    ->create();

    Site::factory(15)->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertCount('sites', 16)
    ->set('site_id', $this->box->room->floor->building->site->id)
    ->assertCount('buildings', 3)
    ->set('building_id', $this->box->room->floor->building->id)
    ->assertCount('floors', 9)
    ->set('floor_id', $this->box->room->floor->id)
    ->assertCount('rooms', 5);
});
