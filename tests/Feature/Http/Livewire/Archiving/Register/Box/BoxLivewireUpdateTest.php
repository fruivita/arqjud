<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Box\BoxLivewireUpdate;
use App\Models\Box;
use App\Models\BoxVolume;
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

    $this->box = Box::factory()->create();

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

test('cannot render box record edit component without specific permission', function () {
    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertForbidden();
});

test('cannot create a box volume without without specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('storeVolume')
    ->assertForbidden();

    expect($this->box->volumes()->doesntExist())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('site_id is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('site_id', '')
    ->call('update')
    ->assertHasNoErrors(['site_id']);
});

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

test('building_id is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('building_id', '')
    ->call('update')
    ->assertHasNoErrors(['building_id']);
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

test('floor_id is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('floor_id', '')
    ->call('update')
    ->assertHasNoErrors(['floor_id']);
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

test('box.stand is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.stand', null)
    ->call('update')
    ->assertHasNoErrors(['box.stand']);
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

test('box.shelf is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.shelf', null)
    ->call('update')
    ->assertHasNoErrors(['box.shelf']);
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

test('description is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.description', '')
    ->call('update')
    ->assertHasNoErrors(['box.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['box.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['box.description' => 'max']);
});

test('box volume number must be between 1 and 50000', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    BoxVolume::factory()
    ->for($this->box, 'box')
    ->create(['number' => 50000]);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('storeVolume')
    ->assertHasErrors(['volume' => 'between']);
});

// Happy path
test('pagination returns the amount of expected box volumes records', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    BoxVolume::factory(120)
    ->for($this->box, 'box')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertCount('volumes', 10)
    ->set('per_page', 10)
    ->assertCount('volumes', 10)
    ->set('per_page', 25)
    ->assertCount('volumes', 25)
    ->set('per_page', 50)
    ->assertCount('volumes', 50)
    ->set('per_page', 100)
    ->assertCount('volumes', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
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

test('emits feedback event when create a box volume record', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    BoxVolume::factory()
    ->for($this->box, 'box')
    ->create(['number' => 10]);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('storeVolume')
    ->assertOk()
    ->assertDispatchedBrowserEvent('notify', [
        'type' => FeedbackType::Success->value,
        'icon' => FeedbackType::Success->icon(),
        'header' => FeedbackType::Success->label(),
        'message' => '11', // 10 + 1 (the number of the volume created)
        'timeout' => 3000,
    ]);
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
    ->for($this->box->room->floor, 'floor')
    ->create();

    Floor::factory(8)
    ->for($this->box->room->floor->building, 'building')
    ->create();

    Building::factory(2)
    ->for($this->box->room->floor->building->site, 'site')
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

test('update a box record with specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.stand', 15)
    ->set('box.shelf', 5)
    ->set('box.description', 'foo bar')
    ->set('box.room_id', $room->id)
    ->call('update')
    ->assertOk();

    $this->box->load('room')->refresh();

    expect($this->box->year)->toBe(2000)
    ->and($this->box->number)->toBe(55)
    ->and($this->box->stand)->toBe(15)
    ->and($this->box->shelf)->toBe(5)
    ->and($this->box->description)->toBe('foo bar')
    ->and($this->box->room->id)->toBe($room->id);
});

test('create a box volume with specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    BoxVolume::factory()
    ->for($this->box, 'box')
    ->create(['number' => 10]);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('storeVolume')
    ->assertOk();

    expect($this->box->volumes()->where('number', 11)->exists())->toBeTrue();
});
