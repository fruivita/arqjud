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

    $this->shelf = Shelf::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a shelf record without being authenticated', function () {
    logout();

    get(route('archiving.register.shelf.edit', $this->shelf))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access shelf record edit route', function () {
    get(route('archiving.register.shelf.edit', $this->shelf))
    ->assertForbidden();
});

test('cannot render shelf record edit component without specific permission', function () {
    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->assertForbidden();
});

test('cannot set the box record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $box = Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->assertOk()
    ->call('markToDelete', $box->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the box record which will be deleted if it has box volumes', function () {
    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $volume = BoxVolume::factory()
    ->for(Box::factory()->for($this->shelf, 'shelf'), 'box')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->assertOk()
    ->call('markToDelete', $volume->box_id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a box record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    $component = Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->call('markToDelete', $box->id)
    ->assertOk();

    revokePermission(PermissionType::BoxDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Box::where('id', $box->id)->exists())->toBeTrue();
});

test('cannot delete a box record if it has box volumes', function () {
    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    $component = Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->call('markToDelete', $box->id)
    ->assertOk();

    BoxVolume::factory()
    ->for($box, 'box')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Box::where('id', $box->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('number is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.number', '')
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'integer']);
});

test('number must be between 1 and 100000', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
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
    Shelf::factory()->create(['number' => 1, 'stand_id' => $stand->id]);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.number', 1)
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertHasErrors(['shelf.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.description', '')
    ->call('update')
    ->assertHasNoErrors(['shelf.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['shelf.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['shelf.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('building_id', '')
    ->call('update')
    ->assertHasErrors(['building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('building_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['building_id' => 'exists']);
});

test('building_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('building_id', $building->id)
    ->assertHasNoErrors()
    ->set('building_id', 'foo')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('floor_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('floor_id', '')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'required']);
});

test('floor_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('floor_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('floor_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('floor_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['floor_id' => 'exists']);
});

test('floor_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $floor = Floor::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('floor_id', $floor->id)
    ->assertHasNoErrors()
    ->set('floor_id', 'foo')
    ->assertHasErrors(['floor_id' => 'integer']);
});

test('room_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('room_id', '')
    ->call('update')
    ->assertHasErrors(['room_id' => 'required']);
});

test('room_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('room_id', 'foo')
    ->call('update')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('room_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('room_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['room_id' => 'exists']);
});

test('room_id is validated in real time', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $room = Room::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('room_id', $room->id)
    ->assertHasNoErrors()
    ->set('room_id', 'foo')
    ->assertHasErrors(['room_id' => 'integer']);
});

test('stand_id is required', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.stand_id', '')
    ->call('update')
    ->assertHasErrors(['shelf.stand_id' => 'required']);
});

test('stand_id must be an integer', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.stand_id', 'foo')
    ->call('update')
    ->assertHasErrors(['shelf.stand_id' => 'integer']);
});

test('stand_id must previously exist in the database', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.stand_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['shelf.stand_id' => 'exists']);
});

// Happy path
test('pagination returns the amount of boxes expected', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Box::factory(120)
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->assertCount('boxes', 10)
    ->set('per_page', 10)
    ->assertCount('boxes', 10)
    ->set('per_page', 25)
    ->assertCount('boxes', 25)
    ->set('per_page', 50)
    ->assertCount('boxes', 50)
    ->set('per_page', 100)
    ->assertCount('boxes', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
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

test('renders edit shelf record component with specific permission', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    get(route('archiving.register.shelf.edit', $this->shelf))
    ->assertOk()
    ->assertSeeLivewire(ShelfLivewireUpdate::class);
});

test('emits feedback event when update a shelf record', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $stand = Stand::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.number', 1)
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a box record', function () {
    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->call('markToDelete', $box->id)
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

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->assertCount('sites', 11);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('floors are available by selecting a building', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $building = Building::factory()
    ->has(Floor::factory(10), 'floors')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('building_id', $building->id)
    ->assertCount('floors', 10);
});

test('rooms are available by selecting a floor', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $floor = Floor::factory()
    ->has(Room::factory(10), 'rooms')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('floor_id', $floor->id)
    ->assertCount('rooms', 10);
});

test('stands are available by selecting a room', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $room = Room::factory()
    ->has(Stand::factory(10), 'stands')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('room_id', $room->id)
    ->assertCount('stands', 10);
});

test('update a shelf record with specific permission', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    $stand = Stand::factory()->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->set('shelf.number', 1)
    ->set('shelf.description', 'foo bar')
    ->set('shelf.stand_id', $stand->id)
    ->call('update')
    ->assertOk();

    $this->shelf->refresh()->load('stand');

    expect($this->shelf->number)->toBe(1)
    ->and($this->shelf->description)->toBe('foo bar')
    ->and($this->shelf->stand->id)->toBe($stand->id);
});

test('defines the box record that will be deleted with specific permission if it has no box volumes', function () {
    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->call('markToDelete', $box->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $box->id);
});

test('delete a box record with specific permission if it has no box volumes', function () {
    grantPermission(PermissionType::ShelfUpdate->value);
    grantPermission(PermissionType::BoxDelete->value);

    $box = Box::factory()
    ->for($this->shelf, 'shelf')
    ->create();

    Livewire::test(ShelfLivewireUpdate::class, ['shelf' => $this->shelf])
    ->call('markToDelete', $box->id)
    ->assertOk()
    ->call('destroy', $box->id)
    ->assertOk();

    expect(Box::where('id', $box->id)->doesntExist())->toBeTrue();
});
