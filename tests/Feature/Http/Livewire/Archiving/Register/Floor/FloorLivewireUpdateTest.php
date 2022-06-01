<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Floor\FloorLivewireUpdate;
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

    $this->floor = Floor::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a floor record without being authenticated', function () {
    logout();

    get(route('archiving.register.floor.edit', $this->floor))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access floor record edit route', function () {
    get(route('archiving.register.floor.edit', $this->floor))
    ->assertForbidden();
});

test('cannot render floor record edit component without specific permission', function () {
    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->assertForbidden();
});

// Rules
test('number is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.number', '')
    ->call('update')
    ->assertHasErrors(['floor.number' => 'required']);
});

test('number must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.number', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.number' => 'integer']);
});

test('number must be between -100 and 300', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.number', -101)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'between'])
    ->set('floor.number', 301)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'between']);
});

test('number and building_id must be unique', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();
    Floor::factory()->create(['number' => 1, 'building_id' => $building->id]);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.number', 1)
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertHasErrors(['floor.number' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.description', '')
    ->call('update')
    ->assertHasNoErrors(['floor.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['floor.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['floor.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('site_id', '')
    ->call('update')
    ->assertHasErrors(['site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('site_id', 10)
    ->call('update')
    ->assertHasErrors(['site_id' => 'exists']);
});

test('site_id is validated in real time', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('site_id', $site->id)
    ->assertHasNoErrors()
    ->set('site_id', 'foo')
    ->assertHasErrors(['site_id' => 'integer']);
});

test('building_id is required', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.building_id', '')
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'required']);
});

test('building_id must be an integer', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.building_id', 'foo')
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'integer']);
});

test('building_id must previously exist in the database', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.building_id', 10)
    ->call('update')
    ->assertHasErrors(['floor.building_id' => 'exists']);
});

// Happy path
test('renders edit floor record component with specific permission', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    get(route('archiving.register.floor.edit', $this->floor))
    ->assertOk()
    ->assertSeeLivewire(FloorLivewireUpdate::class);
});

test('emits feedback event when update a floor record', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.number', 1)
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in floor update', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    Site::factory(10)->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->assertCount('sites', 11);
});

test('buildings are available by selecting a site', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $site = Site::factory()
    ->has(Building::factory(10), 'buildings')
    ->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('site_id', $site->id)
    ->assertCount('buildings', 10);
});

test('update a floor record with specific permission', function () {
    grantPermission(PermissionType::FloorUpdate->value);

    $building = Building::factory()->create();

    Livewire::test(FloorLivewireUpdate::class, ['floor' => $this->floor])
    ->set('floor.number', 1)
    ->set('floor.description', 'foo bar')
    ->set('floor.building_id', $building->id)
    ->call('update')
    ->assertOk();

    $this->floor->refresh()->load('building');

    expect($this->floor->number)->toBe(1)
    ->and($this->floor->description)->toBe('foo bar')
    ->and($this->floor->building->id)->toBe($building->id);
});
