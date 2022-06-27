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

    get(route('archiving.register.box.edit', $this->box->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access box edit route', function () {
    get(route('archiving.register.box.edit', $this->box->id))
    ->assertForbidden();
});

test('cannot render box record edit component without specific permission', function () {
    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->assertForbidden();
});

test('cannot create a box volume without without specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->call('storeVolume')
    ->assertForbidden();

    expect($this->box->volumes()->doesntExist())->toBeTrue();
});

test('cannot update stand if edit mode is disabled', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('cannot update box without specific permission', function () {
    grantPermission(PermissionType::BoxView->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('site_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('building_id', '')
    ->call('update')
    ->assertHasErrors(['building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('building_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('floor_id', '')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'required']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('floor_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('floor_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['floor_id' => 'exists']);
});

test('floor_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('floor_id', $floor->id)
    ->assertHasNoErrors()
    ->set('floor_id', 'foo')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('room_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('room_id', '')
    ->call('update')
    ->assertHasErrors(['room_id' => 'required']);
});

test('room_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('room_id', 'foo')
    ->call('update')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('room_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('room_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['room_id' => 'exists']);
});

test('room_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('room_id', $room->id)
    ->assertHasNoErrors()
    ->set('room_id', 'foo')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('stand_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('stand_id', '')
    ->call('update')
    ->assertHasErrors(['stand_id' => 'required']);
});

test('stand_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('stand_id', 'foo')
    ->call('update')
    ->assertHasErrors(['stand_id' => 'integer']);
});

test('stand_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('stand_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['stand_id' => 'exists']);
});

test('stand_id is validated in real time', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $stand = Stand::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('stand_id', $stand->id)
    ->assertHasNoErrors()
    ->set('stand_id', 'foo')
    ->assertHasErrors(['stand_id' => 'integer']);
});

test('shelf_id is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.shelf_id', '')
    ->call('update')
    ->assertHasErrors(['box.shelf_id' => 'required']);
});

test('shelf_id must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.shelf_id', 'foo')
    ->call('update')
    ->assertHasErrors(['box.shelf_id' => 'integer']);
});

test('shelf_id must previously exist in the database', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.shelf_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['box.shelf_id' => 'exists']);
});

test('year is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.year', '')
    ->call('update')
    ->assertHasErrors(['box.year' => 'required']);
});

test('year must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.year', 'foo')
    ->call('update')
    ->assertHasErrors(['box.year' => 'integer']);
});

test('year must be between 1900 and the current year', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.year', 1899)
    ->call('update')
    ->assertHasErrors(['box.year' => 'between'])
    ->set('box.year', now()->addYear()->format('Y'))
    ->call('update')
    ->assertHasErrors(['box.year' => 'between']);
});

test('number is required', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.number', '')
    ->call('update')
    ->assertHasErrors(['box.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.number', 'foo')
    ->call('update')
    ->assertHasErrors(['box.number' => 'integer']);
});

test('number must be greater then 1', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.number', 0)
    ->call('update')
    ->assertHasErrors(['box.number' => 'min']);
});

test('number and year must be unique', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Box::factory()->create(['year' => 2020, 'number' => 10]);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.year', 2020)
    ->set('box.number', 10)
    ->call('update')
    ->assertHasErrors(['box.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.description', '')
    ->call('update')
    ->assertHasNoErrors(['box.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['box.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['box.description' => 'max']);
});

test('box volume number must be between 1 and 50000', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    BoxVolume::factory()->for($this->box, 'box')->create(['number' => 50000]);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->call('storeVolume')
    ->assertHasErrors(['volume' => 'between']);
});

// Happy path
test('pagination returns the amount of expected box volumes records', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    BoxVolume::factory(30)->for($this->box, 'box')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('per_page', 25)
    ->assertCount('volumes', 25);
});

test('renders edit box record component with view or update permission', function ($permission) {
    grantPermission($permission);

    get(route('archiving.register.box.edit', $this->box->id))
    ->assertOk()
    ->assertSeeLivewire(BoxLivewireUpdate::class);
})->with([
    PermissionType::BoxView->value,
    PermissionType::BoxUpdate->value
]);

test('emits feedback event when update a box record', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $shelf = Shelf::factory()->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.year', 2000)
    ->set('box.number', 10)
    ->set('box.shelf_id', $shelf->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when create a box volume record', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    BoxVolume::factory()->for($this->box, 'box')->create(['number' => 10]);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
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

    $volume = BoxVolume::factory()->for($this->box, 'box')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->call('setToDelete', $volume->id)
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

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->assertCount('sites', 11);
});

test('sets the selected building, floor, room, stand and shelve to null and makes new buildings available when selecting a site', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $site = Site::factory()->has(Building::factory(10), 'buildings')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('site_id', $site->id)
    ->assertSet('box.building_id', null)
    ->assertSet('box.floor_id', null)
    ->assertSet('box.room_id', null)
    ->assertSet('box.stand_id', null)
    ->assertSet('box.shelf_id', null)
    ->assertCount('buildings', 10);
});

test('sets the selected floor, room, stand and shelf to null and makes new floors available when selecting a building', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $building = Building::factory()->has(Floor::factory(10), 'floors')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('building_id', $building->id)
    ->assertSet('box.floor_id', null)
    ->assertSet('box.room_id', null)
    ->assertSet('box.stand_id', null)
    ->assertSet('box.shelf_id', null)
    ->assertCount('floors', 10);
});

test('sets the selected room, stand and shelf to null and makes new rooms available when selecting a floor', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $floor = Floor::factory()->has(Room::factory(10), 'rooms')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('floor_id', $floor->id)
    ->assertSet('box.room_id', null)
    ->assertSet('box.stand_id', null)
    ->assertSet('box.shelf_id', null)
    ->assertCount('rooms', 10);
});

test('sets the selected stand and shelf to null and makes new stands available when selecting a room', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $room = Room::factory()->has(Stand::factory(10), 'stands')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('room_id', $room->id)
    ->assertSet('box.stand_id', null)
    ->assertSet('box.shelf_id', null)
    ->assertCount('stands', 10);
});

test('sets the selected shelf to null and makes new shelves available when selecting a stand', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $stand = Stand::factory()->has(Shelf::factory(10), 'shelves')->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('stand_id', $stand->id)
    ->assertSet('box.shelf_id', null)
    ->assertCount('shelves', 10);
});

test('sites, buildings, floors, rooms, stands and shelves are pre-defined according to the edit box', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    $this->box->load('shelf.stand.room.floor.building.site');

    Shelf::factory(5)->for($this->box->shelf->stand, 'stand')->create();
    Stand::factory(3)->for($this->box->shelf->stand->room, 'room')->create();
    Room::factory(4)->for($this->box->shelf->stand->room->floor, 'floor')->create();
    Floor::factory(8)->for($this->box->shelf->stand->room->floor->building, 'building')->create();
    Building::factory(2)->for($this->box->shelf->stand->room->floor->building->site, 'site')->create();
    Site::factory(15)->create();

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
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

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->set('modo_edicao', true)
    ->set('box.year', 2000)
    ->set('box.number', 55)
    ->set('box.description', 'foo bar')
    ->set('box.shelf_id', $shelf->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->box->refresh();

    expect($this->box->year)->toBe(2000)
    ->and($this->box->number)->toBe(55)
    ->and($this->box->description)->toBe('foo bar')
    ->and($this->box->shelf_id)->toBe($shelf->id);
});

test('create a box volume with specific permission', function () {
    grantPermission(PermissionType::BoxUpdate->value);
    grantPermission(PermissionType::BoxVolumeCreate->value);

    BoxVolume::factory()->for($this->box, 'box')->create(['number' => 10]);

    Livewire::test(BoxLivewireUpdate::class, ['id' => $this->box->id])
    ->call('storeVolume')
    ->assertOk();

    $box_volume = $this->box->volumes()->firstWhere('number', 11);

    expect($box_volume->alias)->toBe('Vol. 11');
});

test('BoxLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(BoxLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
