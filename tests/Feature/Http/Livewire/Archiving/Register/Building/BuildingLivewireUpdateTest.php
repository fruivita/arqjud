<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Building\BuildingLivewireUpdate;
use App\Models\Building;
use App\Models\Site;
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
test('cannot update a building record without being authenticated', function () {
    logout();

    get(route('archiving.register.building.edit', $this->building))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access building record edit route', function () {
    get(route('archiving.register.building.edit', $this->building))
    ->assertForbidden();
});

test('cannot render building record edit component without specific permission', function () {
    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->assertForbidden();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.name', '')
    ->call('update')
    ->assertHasErrors(['building.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.name', ['foo'])
    ->call('update')
    ->assertHasErrors(['building.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.name', Str::random(101))
    ->call('update')
    ->assertHasErrors(['building.name' => 'max']);
});

test('name and site_id must be unique', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $site = Site::factory()->create();
    Building::factory()->create(['name' => 'foo', 'site_id' => $site->id]);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.name', 'foo')
    ->set('building.site_id', $site->id)
    ->call('update')
    ->assertHasErrors(['building.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.description', '')
    ->call('update')
    ->assertHasNoErrors(['building.description']);
});

test('description must be a string', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['building.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['building.description' => 'max']);
});

test('site_id is required', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.site_id', '')
    ->call('update')
    ->assertHasErrors(['building.site_id' => 'required']);
});

test('site_id must be an integer', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.site_id', 'foo')
    ->call('update')
    ->assertHasErrors(['building.site_id' => 'integer']);
});

test('site_id must previously exist in the database', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.site_id', 10)
    ->call('update')
    ->assertHasErrors(['building.site_id' => 'exists']);
});

// Happy path
test('renders edit building record component with specific permission', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    get(route('archiving.register.building.edit', $this->building))
    ->assertOk()
    ->assertSeeLivewire(BuildingLivewireUpdate::class);
});

test('emits feedback event when update a building record', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.name', 'name')
    ->set('building.site_id', $site->id)
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('sites are available for selection in box update', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    Site::factory(10)->create();

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->assertCount('sites', 11);
});

test('update a building record with specific permission', function () {
    grantPermission(PermissionType::BuildingUpdate->value);

    $site = Site::factory()->create();

    Livewire::test(BuildingLivewireUpdate::class, ['building' => $this->building])
    ->set('building.name', 'foo')
    ->set('building.description', 'foo bar')
    ->set('building.site_id', $site->id)
    ->call('update')
    ->assertOk();

    $this->building->refresh()->load('site');

    expect($this->building->name)->toBe('foo')
    ->and($this->building->description)->toBe('foo bar')
    ->and($this->building->site->id)->toBe($site->id);
});
