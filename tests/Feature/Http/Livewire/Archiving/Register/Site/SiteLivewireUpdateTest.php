<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireUpdate;
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

    $this->building = Building::factory()->create();
    $this->building->load('site');

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot update a site record without being authenticated', function () {
    logout();

    get(route('archiving.register.site.edit', $this->building->site))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access site record edit route', function () {
    get(route('archiving.register.site.edit', $this->building->site))
    ->assertForbidden();
});

test('cannot render site record edit component without specific permission', function () {
    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->assertForbidden();
});

test('cannot set the building record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->assertOk()
    ->call('markToDelete', $this->building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the building record which will be deleted if it has floors', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    Floor::factory()
    ->for($this->building, 'building')
    ->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->assertOk()
    ->call('markToDelete', $this->building->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a building record without specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $component = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->call('markToDelete', $this->building->id)
    ->assertOk();

    revokePermission(PermissionType::BuildingDelete->value);

    $component
    ->call('destroy')
    ->assertForbidden();

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();
});

test('cannot delete a building record if it has floors', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $component = Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
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
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('name is required', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.name', '')
    ->call('update')
    ->assertHasErrors(['site.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.name', ['foo'])
    ->call('update')
    ->assertHasErrors(['site.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.name', Str::random(101))
    ->call('update')
    ->assertHasErrors(['site.name' => 'max']);
});

test('name must be unique', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.name', 'foo')
    ->call('update')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.description', '')
    ->call('update')
    ->assertHasNoErrors(['site.description']);
});

test('description must be an string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['site.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['site.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Building::factory(120)
    ->for($this->building->site, 'site')
    ->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
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
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
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

test('renders edit site record component with specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    get(route('archiving.register.site.edit', $this->building->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireUpdate::class);
});

test('emits feedback event when update a site record', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.name', 'foo')
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
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

test('update a site record with specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->set('site.name', 'foo')
    ->set('site.description', 'foo bar')
    ->call('update')
    ->assertOk();

    $this->building->site->refresh();

    expect($this->building->site->name)->toBe('foo')
    ->and($this->building->site->description)->toBe('foo bar');
});

test('defines the building record that will be deleted with specific permission if it has no floors', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->call('markToDelete', $this->building->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->building->id);
});

test('delete a building record with specific permission if it has no floors', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    expect(Building::where('id', $this->building->id)->exists())->toBeTrue();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->building->site])
    ->call('markToDelete', $this->building->id)
    ->assertOk()
    ->call('destroy', $this->building->id)
    ->assertOk();

    expect(Building::where('id', $this->building->id)->doesntExist())->toBeTrue();
});
