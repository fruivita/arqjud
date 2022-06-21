<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireUpdate;
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

    $this->building = Building::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a building record without being authenticated', function () {
    logout();

    get(route('archiving.register.building.edit', $this->building->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building record edit route', function () {
    get(route('archiving.register.building.edit', $this->building->id))
    ->assertForbidden();
});

test('cannot render building record edit component without specific permission', function () {
    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->assertForbidden();
});

test('cannot set the floor record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->assertOk()
    ->call('setToDelete', $floor->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the floor record which will be deleted if it has rooms', function () {
    grantPermission(PermissionType::BuildingUpdate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    Room::factory()->for($floor, 'floor')->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->assertOk()
    ->call('setToDelete', $floor->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a floor record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::BuildingUpdate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    $component = Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->call('setToDelete', $floor->id)
    ->assertOk();

    revokePermission(PermissionType::FloorDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Floor::where('id', $floor->id)->exists())->toBeTrue();
});

test('cannot delete a floor record if it has rooms', function () {
    grantPermission(PermissionType::BuildingUpdate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    $component = Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->call('setToDelete', $floor->id)
    ->assertOk();

    Room::factory()->for($floor, 'floor')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Floor::where('id', $floor->id)->exists())->toBeTrue();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.name', '')
    ->call('update')
    ->assertHasErrors(['building.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.name', ['foo'])
    ->call('update')
    ->assertHasErrors(['building.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.name', Str::random(101))
    ->call('update')
    ->assertHasErrors(['building.name' => 'max']);
});

test('name and site_id must be unique', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $site = Site::factory()->create();
    Building::factory()->create(['name' => 'foo', 'site_id' => $site->id]);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.name', 'foo')
    ->set('building.site_id', $site->id)
    ->call('update')
    ->assertHasErrors(['building.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.description', '')
    ->call('update')
    ->assertHasNoErrors(['building.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['building.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['building.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.site_id', '')
    ->call('update')
    ->assertHasErrors(['building.site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building.site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.site_id', 9090909090)
    ->call('update')
    ->assertHasErrors(['building.site_id' => 'exists']);
});

// Happy path
test('pagination returns the amount of expected floors records', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Floor::factory(30)->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('per_page', 25)
    ->assertCount('floors', 25);
});

test('renders edit building record component with specific permission', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    get(route('archiving.register.building.edit', $this->building->id))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireUpdate::class);
});

test('emits feedback event when update a building record', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.name', 'foo')
    ->set('building.site_id', $site->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a floot record', function () {
    grantPermission(PermissionType::BuildingUpdate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->call('setToDelete', $floor->id)
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

test('sites are available for selection in building update', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Site::factory(10)->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->assertCount('sites', 11);
});

test('update a building record with specific permission', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->set('building.name', 'foo')
    ->set('building.description', 'foo bar')
    ->set('building.site_id', $site->id)
    ->call('update')
    ->assertOk();

    $this->building->refresh();

    expect($this->building->name)->toBe('foo')
    ->and($this->building->description)->toBe('foo bar')
    ->and($this->building->site_id)->toBe($site->id);
});

test('defines the floor record that will be deleted with specific permission if it has no rooms', function () {
    grantPermission(PermissionType::BuildingUpdate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->call('setToDelete', $floor->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $floor->id);
});

test('delete a floor record with specific permission if it has no rooms', function () {
    grantPermission(PermissionType::BuildingUpdate->value);
    grantPermission(PermissionType::FloorDelete->value);

    $floor = Floor::factory()->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireUpdate::class, ['id' => $this->building->id])
    ->call('setToDelete', $floor->id)
    ->assertOk()
    ->call('destroy', $floor->id)
    ->assertOk();

    expect(Floor::where('id', $floor->id)->doesntExist())->toBeTrue();
});

test('BuildingLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(BuildingLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
