<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Shelf\ShelfLivewireUpdate;
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

    $this->shelf = Shelf::factory()->create(['number' => 2]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a shelf record without being authenticated', function () {
    logout();

    get(route('archiving.register.shelf.edit', $this->shelf->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access shelf record edit route', function () {
    get(route('archiving.register.shelf.edit', $this->shelf->id))
    ->assertForbidden();
});

test('cannot render shelf record edit component without specific permission', function () {
    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->assertForbidden();
});

test('cannot update stand if edit mode is disabled', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('cannot update shelf without specific permission', function () {
    grantPermission(PermissionType::ShelfView->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.number', '')
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.number', 0)
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'between'])
    ->set('shelf.number', 100001)
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'between']);
});

test('number and stand_id must be unique', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $stand = Stand::factory()->create();
    Shelf::factory()->create(['number' => 99, 'stand_id' => $stand->id]);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.number', 99)
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'unique']);
});

test('alias is optional', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.alias', '')
    ->call('update')
    ->assertHasNoErrors(['shelf.alias']);
});

test('alias must be a string', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.alias', ['foo'])
    ->call('update')
    ->assertHasErrors(['shelf.alias' => 'string']);
});

test('alias must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.alias', Str::random(101))
    ->call('update')
    ->assertHasErrors(['shelf.alias' => 'max']);
});

test('alias and stand_id must be unique', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $stand = Stand::factory()->create();
    Shelf::factory()->create(['alias' => 99, 'stand_id' => $stand->id]);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.alias', '99')
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertHasErrors(['shelf.alias' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.description', '')
    ->call('update')
    ->assertHasNoErrors(['shelf.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['shelf.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['shelf.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('building_id', '')
    ->call('update')
    ->assertHasErrors(['building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('building_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('floor_id', '')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'required']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('floor_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('floor_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['floor_id' => 'exists']);
});

test('floor_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('floor_id', $floor->id)
    ->assertHasNoErrors()
    ->set('floor_id', 'foo')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('room_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('room_id', '')
    ->call('update')
    ->assertHasErrors(['room_id' => 'required']);
});

test('room_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('room_id', 'foo')
    ->call('update')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('room_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('room_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['room_id' => 'exists']);
});

test('room_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('room_id', $room->id)
    ->assertHasNoErrors()
    ->set('room_id', 'foo')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('stand_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.stand_id', '')
    ->call('update')
    ->assertHasErrors(['shelf.stand_id' => 'required']);
});

test('stand_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.stand_id', 'foo')
    ->call('update')
    ->assertHasErrors(['shelf.stand_id' => 'integer']);
});

test('stand_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.stand_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['shelf.stand_id' => 'exists']);
});

// Happy path
test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Box::factory(30)->for($this->shelf, 'shelf')->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('per_page', 25)
    ->assertCount('boxes', 25);
});

test('renders edit shelf record component with view or update permission', function ($permission) {
    grantPermission($permission);

    get(route('archiving.register.shelf.edit', $this->shelf->id))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireUpdate::class);
})->with([
    PermissionType::ShelfView->value,
    PermissionType::ShelfUpdate->value
]);

test('emits feedback event when update a shelf record', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $stand = Stand::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.number', 1)
    ->set('shelf.alias', '1')
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a box record', function () {
    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()->for($this->shelf, 'shelf')->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->call('setToDelete', $box->id)
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

test('sites are available for selection in shelf update', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Site::factory(10)->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->assertCount('sites', 11);
});

test('sets the selected building, floor, room and stand to null and makes new buildings available when selecting a site', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $site = Site::factory()->has(Building::factory(10), 'buildings')->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('site_id', $site->id)
    ->assertSet('shelf.building_id', null)
    ->assertSet('shelf.floor_id', null)
    ->assertSet('shelf.room_id', null)
    ->assertSet('shelf.stand_id', null)
    ->assertCount('buildings', 10);
});

test('sets the selected floor, room and stand to null and makes new floors available when selecting a building', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $building = Building::factory()->has(Floor::factory(10), 'floors')->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('building_id', $building->id)
    ->assertSet('shelf.floor_id', null)
    ->assertSet('shelf.room_id', null)
    ->assertSet('shelf.stand_id', null)
    ->assertCount('floors', 10);
});

test('sets the selected room and stand to null and makes new rooms available when selecting a floor', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $floor = Floor::factory()->has(Room::factory(10), 'rooms')->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('floor_id', $floor->id)
    ->assertSet('shelf.room_id', null)
    ->assertSet('shelf.stand_id', null)
    ->assertCount('rooms', 10);
});

test('sets the selected stand to null and makes new stands available when selecting a room', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $room = Room::factory()->has(Stand::factory(10), 'stands')->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('room_id', $room->id)
    ->assertSet('shelf.stand_id', null)
    ->assertCount('stands', 10);
});

test('sites, buildings, floors, rooms, and stands are pre-defined according to the edit shelf', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $this->shelf->load('stand.room.floor.building.site');

    Stand::factory(3)->for($this->shelf->stand->room, 'room')->create();
    Room::factory(4)->for($this->shelf->stand->room->floor, 'floor')->create();
    Floor::factory(8)->for($this->shelf->stand->room->floor->building, 'building')->create();
    Building::factory(2)->for($this->shelf->stand->room->floor->building->site, 'site')->create();
    Site::factory(15)->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->assertCount('sites', 16)
    ->assertSet('site_id', $this->shelf->stand->room->floor->building->site->id)
    ->assertCount('buildings', 3)
    ->assertSet('building_id', $this->shelf->stand->room->floor->building->id)
    ->assertCount('floors', 9)
    ->assertSet('floor_id', $this->shelf->stand->room->floor->id)
    ->assertCount('rooms', 5)
    ->assertSet('room_id', $this->shelf->stand->room->id)
    ->assertCount('stands', 4);
});

test('update a shelf record with specific permission', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $stand = Stand::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['id' => $this->shelf->id])
    ->set('modo_edicao', true)
    ->set('shelf.number', 99)
    ->set('shelf.alias', '99')
    ->set('shelf.description', 'foo bar')
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->shelf->refresh();

    expect($this->shelf->number)->toBe(99)
    ->and($this->shelf->alias)->toBe('99')
    ->and($this->shelf->description)->toBe('foo bar')
    ->and($this->shelf->stand_id)->toBe($stand->id);
});

test('ShelfLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(ShelfLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
            \App\Http\Livewire\Traits\ConverteStringVaziaEmNull::class,
        ])
    )->toBeTrue();
});
