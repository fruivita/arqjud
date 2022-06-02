<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireIndex;
use App\Models\Building;
use App\Models\Floor;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot list building records without being authenticated', function () {
    logout();

    get(route('archiving.register.building.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building records listing route', function () {
    get(route('archiving.register.building.index'))
    ->assertForbidden();
});

test('cannot render listing component from building records without specific permission', function () {
    Livewire::test(BuildingLivewireIndex::class)->assertForbidden();
});

test('cannot set the building record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Building());
});

test('cannot set the building record which will be deleted if he has floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()
    ->has(Floor::factory(2), 'floors')
    ->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', new Building());
});

test('cannot delete a building record without specific permission', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    $building = Building::factory()->create(['name' => 'foo']);

    Livewire::test(BuildingLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $building->id)
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('name', 'foo')->exists())->toBeTrue();
});

test('cannot delete a building record if he has floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    $component = Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $building->id)
    ->assertOk();

    $floors = Floor::factory(2)->make();

    $building->floors()->saveMany($floors);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $building->id)->get())->toHaveCount(1);
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Livewire::test(BuildingLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Building::factory(120)->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->assertCount('buildings', 10)
    ->set('per_page', 10)
    ->assertCount('buildings', 10)
    ->set('per_page', 25)
    ->assertCount('buildings', 25)
    ->set('per_page', 50)
    ->assertCount('buildings', 50)
    ->set('per_page', 100)
    ->assertCount('buildings', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Livewire::test(BuildingLivewireIndex::class)
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

test('lists building records with specific permission', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    get(route('archiving.register.building.index'))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireIndex::class);
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create(['name' => 'foo']);

    Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $building->id)
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

test('defines the building record that will be deleted with specific permission if it has no floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create(['name' => 'foo']);

    Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $building->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $building->id);
});

test('deletes a building record with specific permission if it has no floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create(['name' => 'foo']);

    expect(Building::where('name', 'foo')->exists())->toBeTrue();

    Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $building->id)
    ->assertOk()
    ->call('destroy', $building->id)
    ->assertOk();

    expect(Building::where('name', 'foo')->doesntExist())->toBeTrue();
});
