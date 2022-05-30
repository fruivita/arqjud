<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireCreate;
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

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot create a floor record without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor record creation route', function () {
    get(route('archiving.register.floor.create'))
    ->assertForbidden();
});

test('cannot render floor record creation component without specific permission', function () {
    Livewire::test(FloorLivewireCreate::class)
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.number', '')
    ->call('store')
    ->assertHasErrors(['floor.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.number', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.number' => 'integer']);
});

test('number must be between -100 and 300', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.number', -101)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'between'])
    ->set('floor.number', 301)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'between']);
});

test('number and building_id must be unique', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $building = Building::factory()->create();
    Floor::factory()->create(['number' => 1, 'building_id' => $building->id]);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.number', 1)
    ->set('building_id', $building->id)
    ->call('store')
    ->assertHasErrors(['floor.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.description', '')
    ->call('store')
    ->assertHasNoErrors(['floor.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['floor.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['floor.description' => 'max']);
});

test('site_id is optional', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('site_id', '')
    ->call('store')
    ->assertHasNoErrors(['site_id']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('site_id', 'foo')
    ->call('store')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('site_id', 10)
    ->call('store')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $site = Site::factory()->create();

    Livewire::test(FloorLivewireCreate::class)
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('building_id', '')
    ->call('store')
    ->assertHasErrors(['building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('building_id', 'foo')
    ->call('store')
    ->assertHasErrors(['building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Livewire::test(FloorLivewireCreate::class)
    ->set('building_id', 10)
    ->call('store')
    ->assertHasErrors(['building_id' => 'exists']);
});

// Happy path
test('renders floor record creation component with specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    get(route('archiving.register.floor.create'))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireCreate::class);
});

test('emits feedback event when creates a floor record', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.number', 1)
    ->set('building_id', $building->id)
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in floor creation', function () {
    grantPermission(PermissionType::FloorCreate->value);

    Site::factory(10)->create();

    Livewire::test(FloorLivewireCreate::class)
    ->assertCount('sites', 10);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(FloorLivewireCreate::class)
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('creates a floor record with specific permission', function () {
    grantPermission(PermissionType::FloorCreate->value);

    $building = Building::factory()->create();

    expect(Floor::count())->toBe(0);

    Livewire::test(FloorLivewireCreate::class)
    ->set('floor.number', 1)
    ->set('floor.description', 'foo bar')
    ->set('building_id', $building->id)
    ->call('store')
    ->assertOk();

    $floor = Floor::with('building')->first();

    expect($floor->number)->toBe(1)
    ->and($floor->description)->toBe('foo bar')
    ->and($floor->building->id)->toBe($building->id);
});
