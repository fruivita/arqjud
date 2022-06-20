<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireShow;
use App\Models\Building;
use App\Models\Floor;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot individually view a building without being authenticated', function () {
    logout();

    get(route('archiving.register.building.show', $this->building->id))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual building view route', function () {
    get(route('archiving.register.building.show', $this->building->id))
    ->assertForbidden();
});

test('cannot render individual building view component without specific permission', function () {
    Livewire::test(BuildingLivewireShow::class, ['id' => $this->building->id])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BuildingView->value);

    Livewire::test(BuildingLivewireShow::class, ['id' => $this->building->id])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual building view component with specific permission', function () {
    grantPermission(PermissionType::BuildingView->value);

    get(route('archiving.register.building.show', $this->building->id))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireShow::class);
});

test('hierarchical data is set', function () {
    grantPermission(PermissionType::BuildingView->value);

    Livewire::test(BuildingLivewireShow::class, ['id' => $this->building->id])
    ->assertSet('hierarchy', $this->building->hierarchicalData());
});

test('pagination returns the amount of floors expected', function () {
    grantPermission(PermissionType::BuildingView->value);

    Floor::factory(120)->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireShow::class, ['id' => $this->building->id])
    ->assertCount('floors', 10)
    ->set('per_page', 10)
    ->assertCount('floors', 10)
    ->set('per_page', 25)
    ->assertCount('floors', 25)
    ->set('per_page', 50)
    ->assertCount('floors', 50)
    ->set('per_page', 100)
    ->assertCount('floors', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::BuildingView->value);

    Livewire::test(BuildingLivewireShow::class, ['id' => $this->building->id])
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

test('individually view a building with specific permission', function () {
    grantPermission(PermissionType::BuildingView->value);

    get(route('archiving.register.building.show', $this->building->id))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireShow::class);
});

test('BuildingLivewireShow uses the withsorting trait', function () {
    expect(
        collect(class_uses(BuildingLivewireShow::class))
        ->contains(\App\Http\Livewire\Traits\WithSorting::class)
    )->toBeTrue();
});
