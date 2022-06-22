<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Stand\StandLivewireUpdate;
use App\Models\Box;
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

    $this->stand = Stand::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a stand record without being authenticated', function () {
    logout();

    get(route('archiving.register.stand.edit', $this->stand->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access stand record edit route', function () {
    get(route('archiving.register.stand.edit', $this->stand->id))
    ->assertForbidden();
});

test('cannot render stand record edit component without specific permission', function () {
    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.number', '')
    ->call('update')
    ->assertHasErrors(['stand.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['stand.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.number', 0)
    ->call('update')
    ->assertHasErrors(['stand.number' => 'between'])
    ->set('stand.number', 100001)
    ->call('update')
    ->assertHasErrors(['stand.number' => 'between']);
});

test('number and room_id must be unique', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $room = Room::factory()->create();
    Stand::factory()->create(['number' => 99, 'room_id' => $room->id]);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.number', 99)
    ->set('stand.room_id', $room->id)
    ->call('update')
    ->assertHasErrors(['stand.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.description', '')
    ->call('update')
    ->assertHasNoErrors(['stand.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['stand.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['stand.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('building_id', '')
    ->call('update')
    ->assertHasErrors(['building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('building_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id is required', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('floor_id', '')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'required']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('floor_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('floor_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['floor_id' => 'exists']);
});

test('floor_id is validated in real time', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('floor_id', $floor->id)
    ->assertHasNoErrors()
    ->set('floor_id', 'foo')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('room_id is required', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.room_id', '')
    ->call('update')
    ->assertHasErrors(['stand.room_id' => 'required']);
});

test('room_id must be an integer', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.room_id', 'foo')
    ->call('update')
    ->assertHasErrors(['stand.room_id' => 'integer']);
});

test('room_id must previously exist in the database', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.room_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['stand.room_id' => 'exists']);
});

// Happy path
test('pagination returns the amount of shelves expected', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Shelf::factory(30)->for($this->stand, 'stand')->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('per_page', 25)
    ->assertCount('shelves', 25);
});

test('renders edit stand record component with specific permission', function () {
    grantPermission(PermissionType::StandUpdate->value);

    get(route('archiving.register.stand.edit', $this->stand->id))
    ->assertOk()
    ->assertSeeLivewire(StandLivewireUpdate::class);
});

test('emits feedback event when update a stand record', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.number', 1)
    ->set('stand.room_id', $room->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a shelf record', function () {
    grantPermission(PermissionType::StandUpdate->value);
    grantPermission(PermissionType::ShelfDelete->value);

    $shelf = Shelf::factory()->for($this->stand, 'stand')->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->call('setToDelete', $shelf->id)
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

test('sites are available for selection in stand update', function () {
    grantPermission(PermissionType::StandUpdate->value);

    Site::factory(10)->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->assertCount('sites', 11);
});

test('sets the selected building, floor and room to null and makes new buildings available when selecting a site', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $site = Site::factory()->has(Building::factory(10), 'buildings')->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('site_id', $site->id)
    ->assertSet('stand.building_id', null)
    ->assertSet('stand.floor_id', null)
    ->assertSet('stand.room_id', null)
    ->assertCount('buildings', 10);
});

test('sets the selected floor and room to null and makes new floors available when selecting a building', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $building = Building::factory()->has(Floor::factory(10), 'floors')->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('building_id', $building->id)
    ->assertSet('stand.floor_id', null)
    ->assertSet('stand.room_id', null)
    ->assertCount('floors', 10);
});

test('sets the selected room to null and makes new rooms available when selecting a floor', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $floor = Floor::factory()->has(Room::factory(10), 'rooms')->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('floor_id', $floor->id)
    ->assertSet('stand.room_id', null)
    ->assertCount('rooms', 10);
});

test('sites, buildings, floors and rooms are pre-defined according to the edit stand', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $this->stand->load('room.floor.building.site');

    Room::factory(4)->for($this->stand->room->floor, 'floor')->create();
    Floor::factory(8)->for($this->stand->room->floor->building, 'building')->create();
    Building::factory(2)->for($this->stand->room->floor->building->site, 'site')->create();
    Site::factory(15)->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->assertCount('sites', 16)
    ->assertSet('site_id', $this->stand->room->floor->building->site->id)
    ->assertCount('buildings', 3)
    ->assertSet('building_id', $this->stand->room->floor->building->id)
    ->assertCount('floors', 9)
    ->assertSet('floor_id', $this->stand->room->floor->id)
    ->assertCount('rooms', 5);
});

test('update a stand record with specific permission', function () {
    grantPermission(PermissionType::StandUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(StandLivewireUpdate::class, ['id' => $this->stand->id])
    ->set('stand.number', 99)
    ->set('stand.description', 'foo bar')
    ->set('stand.room_id', $room->id)
    ->call('update')
    ->assertOk();

    $this->stand->refresh();

    expect($this->stand->number)->toBe(99)
    ->and($this->stand->description)->toBe('foo bar')
    ->and($this->stand->room_id)->toBe($room->id);
});

test('StandLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(StandLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
