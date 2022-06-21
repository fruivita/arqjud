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

// Happy path
test('renders individual building view component with specific permission', function () {
    grantPermission(PermissionType::BuildingView->value);

    get(route('archiving.register.building.show', $this->building->id))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireShow::class);
});

test('pagination returns the amount of floors expected', function () {
    grantPermission(PermissionType::BuildingView->value);

    Floor::factory(30)->for($this->building, 'building')->create();

    Livewire::test(BuildingLivewireShow::class, ['id' => $this->building->id])
    ->set('per_page', 25)
    ->assertCount('floors', 25);
});

test('individually view a building with specific permission', function () {
    grantPermission(PermissionType::BuildingView->value);

    get(route('archiving.register.building.show', $this->building->id))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireShow::class);
});

test('BuildingLivewireShow uses trait', function () {
    expect(
        collect(class_uses(BuildingLivewireShow::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
