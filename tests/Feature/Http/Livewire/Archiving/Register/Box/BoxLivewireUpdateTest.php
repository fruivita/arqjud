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
use App\Models\Shelf;
use App\Models\Site;
use App\Models\Stand;
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

test('cannot set the box volume record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $volume = BoxVolume::factory()
    ->for($this->box, 'box')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertOk()
    ->call('markToDelete', $volume->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a box volume record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeDelete->value);

    $volume = BoxVolume::factory()
    ->for($this->box, 'box')
    ->create();

    $component = Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('markToDelete', $volume->id)
    ->assertOk();

    revokePermission(PermissionType::BoxVolumeDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(BoxVolume::where('id', $volume->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
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
    ->set('site_id', 9090909090)
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

test('building_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('building_id', '')
    ->call('update')
    ->assertHasErrors(['building_id' => 'required']);
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
    ->set('building_id', 9090909090)
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

test('floor_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('floor_id', '')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'required']);
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
    ->set('floor_id', 9090909090)
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

test('room_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('room_id', '')
    ->call('update')
    ->assertHasErrors(['room_id' => 'required']);
});

test('room_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('room_id', 'foo')
    ->call('update')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('room_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('room_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['room_id' => 'exists']);
});

test('room_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('room_id', $room->id)
    ->assertHasNoErrors()
    ->set('room_id', 'foo')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('stand_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('stand_id', '')
    ->call('update')
    ->assertHasErrors(['stand_id' => 'required']);
});

test('stand_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('stand_id', 'foo')
    ->call('update')
    ->assertHasErrors(['stand_id' => 'integer']);
});

test('stand_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('stand_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['stand_id' => 'exists']);
});

test('stand_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $stand = Stand::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('stand_id', $stand->id)
    ->assertHasNoErrors()
    ->set('stand_id', 'foo')
    ->assertHasErrors(['stand_id' => 'integer']);
});

test('box.shelf_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.shelf_id', '')
    ->call('update')
    ->assertHasErrors(['box.shelf_id' => 'required']);
});

test('box.shelf_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.shelf_id', 'foo')
    ->call('update')
    ->assertHasErrors(['box.shelf_id' => 'integer']);
});

test('box.shelf_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.shelf_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['box.shelf_id' => 'exists']);
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

    $shelf = Shelf::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 2000)
    ->set('box.number', 10)
    ->set('box.shelf_id', $shelf->id)
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

test('emits feedback event when deleting a box volume record', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeDelete->value);

    $volume = BoxVolume::factory()
    ->for($this->box, 'box')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('markToDelete', $volume->id)
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

test('stands are available by selecting a room', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()
    ->has(Stand::factory(10), 'stands')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('room_id', $room->id)
    ->assertCount('stands', 10);
});

test('shelves are available by selecting a stand', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $stand = Stand::factory()
    ->has(Shelf::factory(10), 'shelves')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('stand_id', $stand->id)
    ->assertCount('shelves', 10);
});

test('sites, buildings, floors, rooms, stands and shelves are pre-selected according to the edit box', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $this->box->load('shelf.stand.room.floor.building.site');

    Shelf::factory(5)
    ->for($this->box->shelf->stand, 'stand')
    ->create();

    Stand::factory(3)
    ->for($this->box->shelf->stand->room, 'room')
    ->create();

    Room::factory(4)
    ->for($this->box->shelf->stand->room->floor, 'floor')
    ->create();

    Floor::factory(8)
    ->for($this->box->shelf->stand->room->floor->building, 'building')
    ->create();

    Building::factory(2)
    ->for($this->box->shelf->stand->room->floor->building->site, 'site')
    ->create();

    Site::factory(15)->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->assertCount('sites', 16)
    ->assertSet('site_id', $this->box->shelf->stand->room->floor->building->site->id)
    ->assertCount('buildings', 3)
    ->assertSet('building_id', $this->box->shelf->stand->room->floor->building->id)
    ->assertCount('floors', 9)
    ->assertSet('floor_id', $this->box->shelf->stand->room->floor->id)
    ->assertCount('rooms', 5)
    ->assertSet('room_id', $this->box->shelf->stand->room->id)
    ->assertCount('stands', 4)
    ->assertSet('stand_id', $this->box->shelf->stand->id)
    ->assertCount('shelves', 6);
});

test('update a box record with specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $shelf = Shelf::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.description', 'foo bar')
    ->set('box.shelf_id', $shelf->id)
    ->call('update')
    ->assertOk();

    $this->box->load('shelf')->refresh();

    expect($this->box->year)->toBe(2000)
    ->and($this->box->number)->toBe(55)
    ->and($this->box->description)->toBe('foo bar')
    ->and($this->box->shelf->id)->toBe($shelf->id);
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

test('delete a box volume record with specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeDelete->value);

    $volume = BoxVolume::factory()
    ->for($this->box, 'box')
    ->create();

    Livewire::test(BoxLivewireUpdate::class, ['box' => $this->box])
    ->call('markToDelete', $volume->id)
    ->assertOk()
    ->call('destroy', $volume->id)
    ->assertOk();

    expect(BoxVolume::where('id', $volume->id)->doesntExist())->toBeTrue();
});
