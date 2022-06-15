<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireUpdate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Site;
use App\Models\Stand;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->room = Room::factory()->create();
    $this->room->load('floor.building.site');

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a floor record without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.edit', $this->room->floor))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor record edit route', function () {
    get(route('archiving.register.floor.edit', $this->room->floor))
    ->assertForbidden();
});

test('cannot render floor record edit component without specific permission', function () {
    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->assertForbidden();
});

test('cannot set the room record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->assertOk()
    ->call('markToDelete', $this->room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the room record which will be deleted if it has stands', function () {
    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    Stand::factory()
    ->for($this->room, 'room')
    ->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->assertOk()
    ->call('markToDelete', $this->room->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a room record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $component = Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->call('markToDelete', $this->room->id)
    ->assertOk();

    revokePermission(PermissionType::RoomDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $this->room->id)->exists())->toBeTrue();
});

test('cannot delete a room record if it has stands', function () {
    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    $component = Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->call('markToDelete', $this->room->id)
    ->assertOk();

    Stand::factory()
    ->for($this->room, 'room')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Room::where('id', $this->room->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('number is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.number', '')
    ->call('update')
    ->assertHasErrors(['floor.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.number' => 'integer']);
});

test('number must be between -100 and 300', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.number', -101)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'between'])
    ->set('floor.number', 301)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'between']);
});

test('number and building_id must be unique', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();
    Floor::factory()->create(['number' => 99, 'building_id' => $building->id]);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.number', 99)
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.description', '')
    ->call('update')
    ->assertHasNoErrors(['floor.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['floor.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.building_id', '')
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.building_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'exists']);
});

// Happy path
test('pagination returns the amount of expected rooms records', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Room::factory(120)
    ->for($this->room->floor, 'floor')
    ->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->assertCount('rooms', 10)
    ->set('per_page', 10)
    ->assertCount('rooms', 10)
    ->set('per_page', 25)
    ->assertCount('rooms', 25)
    ->set('per_page', 50)
    ->assertCount('rooms', 50)
    ->set('per_page', 100)
    ->assertCount('rooms', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
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

test('renders edit floor record component with specific permission', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    get(route('archiving.register.floor.edit', $this->room->floor))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireUpdate::class);
});

test('emits feedback event when update a floor record', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.number', 1)
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a room record', function () {
    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->call('markToDelete', $this->room->id)
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

test('sites are available for selection in floor update', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Site::factory(10)->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->assertCount('sites', 11);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('update a floor record with specific permission', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->set('floor.number', 99)
    ->set('floor.description', 'foo bar')
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertOk();

    $this->room->floor->refresh()->load('building');

    expect($this->room->floor->number)->toBe(99)
    ->and($this->room->floor->description)->toBe('foo bar')
    ->and($this->room->floor->building->id)->toBe($building->id);
});

test('defines the room record that will be deleted with specific permission if it has no stands', function () {
    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->call('markToDelete', $this->room->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->room->id);
});

test('delete a room record with specific permission if it has no stands', function () {
    grantPermission(PermissionType::FloorUpdate->value);
    grantPermission(PermissionType::RoomDelete->value);

    expect(Room::where('id', $this->room->id)->exists())->toBeTrue();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->room->floor])
    ->call('markToDelete', $this->room->id)
    ->assertOk()
    ->call('destroy', $this->room->id)
    ->assertOk();

    expect(Room::where('id', $this->room->id)->doesntExist())->toBeTrue();
});
