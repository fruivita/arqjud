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

    $this->building = Building::factory()->create();
    $this->building->load('site');

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

    Livewire::test(BuildingLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $this->building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the building record which will be deleted if it has floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    Floor::factory()
    ->for($this->building, 'building')
    ->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->assertOk()
    ->call('markToDelete', $this->building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a building record without specific permission', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $component = Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $this->building->id)
    ->assertOk();

    revokePermission(PermissionType::BuildingDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();
});

test('cannot delete a building record if it has floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $component = Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $this->building->id)
    ->assertOk();

    Floor::factory()
    ->for($this->building, 'building')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();
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

    Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $this->building->id)
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

    Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $this->building->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->building->id);
});

test('delete a building record with specific permission if it has no floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();

    Livewire::test(BuildingLivewireIndex::class)
    ->call('markToDelete', $this->building->id)
    ->assertOk()
    ->call('destroy', $this->building->id)
    ->assertOk();

    expect(Building::where('id', $this->building->id)->doesntExist())->toBeTrue();
});
