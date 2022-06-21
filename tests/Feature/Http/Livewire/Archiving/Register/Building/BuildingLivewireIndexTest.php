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
    ->call('setToDelete', $this->building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the building record which will be deleted if it has floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    Floor::factory()->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a building record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $component = Livewire::test(BuildingLivewireIndex::class)
    ->call('setToDelete', $this->building->id)
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
    ->call('setToDelete', $this->building->id)
    ->assertOk();

    Floor::factory()->for($this->building, 'building')->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    Building::factory(30)->create();

    Livewire::test(BuildingLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('buildings', 25);
});

test('lists building records with specific permission', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    get(route('archiving.register.building.index'))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::BuildingViewAny->value);

    $this->building->delete();

    Building::factory()->create(['name' => 'foo']);
    Building::factory()->create(['name' => 'bar baz']); // contains bar
    Building::factory()->create(['name' => 'bar']);

    Livewire::test(BuildingLivewireIndex::class)
    ->set('term', 'foo')
    ->assertCount('buildings', 1)
    ->set('term', 'bar')
    ->assertCount('buildings', 2)
    ->set('term', '')
    ->assertCount('buildings', 3);
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    Livewire::test(BuildingLivewireIndex::class)
    ->call('setToDelete', $this->building->id)
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
    ->call('setToDelete', $this->building->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->building->id);
});

test('delete a building record with specific permission if it has no floors', function () {
    grantPermission(PermissionType::BuildingViewAny->value);
    grantPermission(PermissionType::BuildingDelete->value);

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();

    Livewire::test(BuildingLivewireIndex::class)
    ->call('setToDelete', $this->building->id)
    ->assertOk()
    ->call('destroy', $this->building->id)
    ->assertOk();

    expect(Building::where('id', $this->building->id)->doesntExist())->toBeTrue();
});

test('BuildingLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(BuildingLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
