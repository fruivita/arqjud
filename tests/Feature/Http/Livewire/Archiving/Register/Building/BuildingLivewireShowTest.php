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
test('it is not possible to individually view a building without being authenticated', function () {
    logout();

    get(route('archiving.register.building.show', $this->building))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual building view route', function () {
    get(route('archiving.register.building.show', $this->building))
    ->assertForbidden();
});

test('cannot render individual building view component without specific permission', function () {
    Livewire::test(BuildingLivewireShow::class, ['building' => $this->building])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BuildingView->value);

    Livewire::test(BuildingLivewireShow::class, ['building' => $this->building])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual building view component with specific permission', function () {
    grantPermission(PermissionType::BuildingView->value);

    get(route('archiving.register.building.show', $this->building))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireShow::class);
});

test('pagination returns the amount of floors expected', function () {
    grantPermission(PermissionType::BuildingView->value);

    Floor::factory(120)
    ->for($this->building, 'building')
    ->create();

    Livewire::test(BuildingLivewireShow::class, ['building' => $this->building])
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

    Livewire::test(BuildingLivewireShow::class, ['building' => $this->building])
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

    get(route('archiving.register.building.show', $this->building))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireShow::class);
});

test('next and previous are available when viewing individual buildings, even when dealing with the first or last record', function () {
    $this->building->delete();
    grantPermission(PermissionType::BuildingView->value);

    $first = Building::factory()->create(['name' => 'bar']);
    $second = Building::factory()->create(['name' => 'baz']);
    $last = Building::factory()->create(['name' => 'foo']);

    // has previous and next
    Livewire::test(BuildingLivewireShow::class, ['building' => $second])
    ->assertSet('previous', $first->id)
    ->assertSet('next', $last->id);

    // only has next
    Livewire::test(BuildingLivewireShow::class, ['building' => $first])
    ->assertSet('previous', null)
    ->assertSet('next', $second->id);

    // has only previous
    Livewire::test(BuildingLivewireShow::class, ['building' => $last])
    ->assertSet('previous', $second->id)
    ->assertSet('next', null);
});
