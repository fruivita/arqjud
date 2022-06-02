<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireCreate;
use App\Models\Building;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Str;
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
test('cannot create a building record without being authenticated', function () {
    logout();

    get(route('archiving.register.building.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building record creation route', function () {
    get(route('archiving.register.building.create'))
    ->assertForbidden();
});

test('cannot render building record creation component without specific permission', function () {
    Livewire::test(BuildingLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', '')
    ->call('store')
    ->assertHasErrors(['building.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', ['foo'])
    ->call('store')
    ->assertHasErrors(['building.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', Str::random(101))
    ->call('store')
    ->assertHasErrors(['building.name' => 'max']);
});

test('name and site_id must be unique', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $site = Site::factory()->create();
    Building::factory()->create(['name' => 'foo', 'site_id' => $site->id]);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', 'foo')
    ->set('building.site_id', $site->id)
    ->call('store')
    ->assertHasErrors(['building.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.description', '')
    ->call('store')
    ->assertHasNoErrors(['building.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['building.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['building.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.site_id', '')
    ->call('store')
    ->assertHasErrors(['building.site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.site_id', 'foo')
    ->call('store')
    ->assertHasErrors(['building.site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.site_id', 10)
    ->call('store')
    ->assertHasErrors(['building.site_id' => 'exists']);
});

// Happy path
test('renders building record creation component with specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    get(route('archiving.register.building.create'))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireCreate::class);
});

test('emits feedback event when creates a building record', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $site = Site::factory()->create();

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', 'name')
    ->set('building.site_id', $site->id)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in box creation', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    Site::factory(10)->create();

    Livewire::test(BuildingLivewireCreate::class)
    ->assertCount('sites', 10);
});

test('creates a building record with specific permission', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $site = Site::factory()->create();

    expect(Building::count())->toBe(0);

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', 'foo')
    ->set('building.description', 'foo bar')
    ->set('building.site_id', $site->id)
    ->call('store')
    ->assertOk();

    $building = Building::with('site')->first();

    expect($building->name)->toBe('foo')
    ->and($building->description)->toBe('foo bar')
    ->and($building->site->id)->toBe($site->id);
});

test('reset to a blank model after the building is created', function () {
    grantPermission(PermissionType::BuildingCreate->value);

    $site = Site::factory()->create();

    $blank = new Building();

    Livewire::test(BuildingLivewireCreate::class)
    ->set('building.name', 'foo')
    ->set('building.site_id', $site->id)
    ->call('store')
    ->assertOk()
    ->assertSet('building', $blank);
});
