<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireCreate;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
use Livewire\Livewire;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->site = Site::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a building record without being authenticated', function () {
    logout();

    get(route('archiving.register.building.create', $this->site))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building record creation route', function () {
    get(route('archiving.register.building.create', $this->site))
    ->assertForbidden();
});

test('cannot render building record creation component without specific permission', function () {
    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->assertForbidden();
});

test('cannot set the building record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->assertOk()
    ->call('markToDelete', $building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the building record which will be deleted if it has floors', function () {
    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    Floor::factory()
    ->for($building, 'building')
    ->create();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->assertOk()
    ->call('markToDelete', $building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a building record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    $component = Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->call('markToDelete', $building->id)
    ->assertOk();

    revokePermission(PermissionType::BuildingDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $building->id)->exists())->toBeTrue();
});

test('cannot delete a building record if it has floors', function () {
    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    $component = Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->call('markToDelete', $building->id)
    ->assertOk();

    Floor::factory()
    ->for($building, 'building')
    ->create();

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $building->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('name is required', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', '')
    ->call('store')
    ->assertHasErrors(['building.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', ['foo'])
    ->call('store')
    ->assertHasErrors(['building.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', Str::random(101))
    ->call('store')
    ->assertHasErrors(['building.name' => 'max']);
});

test('name and site_id must be unique', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Building::factory()->create(['name' => 'foo', 'site_id' => $this->site->id]);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', 'foo')
    ->call('store')
    ->assertHasErrors(['building.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.description', '')
    ->call('store')
    ->assertHasNoErrors(['building.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['building.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['building.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Building::factory(120)
    ->for($this->site, 'site')
    ->create();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
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
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
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

test('renders building record creation component with specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    get(route('archiving.register.building.create', $this->site))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireCreate::class);
});

test('emits feedback event when creates a building record', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', 'name')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
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

test('creates a building record with specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', 'foo')
    ->set('building.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $building = Building::with('site')->first();

    expect($building->name)->toBe('foo')
    ->and($building->description)->toBe('foo bar')
    ->and($building->site->id)->toBe($this->site->id);
});

test('reset to a blank model after the building is created', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $blank = new Building();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->set('building.name', 'foo')
    ->call('store')
    ->assertOk()
    ->assertSet('building', $blank);
});

test('defines the building record that will be deleted with specific permission if it has no floors', function () {
    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->call('markToDelete', $building->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $building->id);
});

test('delete a building record with specific permission if it has no floors', function () {
    grantPermission(PermissionType::BuildingCreate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->create();

    Livewire::test(BuildingLivewireCreate::class, ['site' => $this->site])
    ->call('markToDelete', $building->id)
    ->assertOk()
    ->call('destroy', $building->id)
    ->assertOk();

    expect(Building::where('id', $building->id)->doesntExist())->toBeTrue();
});
