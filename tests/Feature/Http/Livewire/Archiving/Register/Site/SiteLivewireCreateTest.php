<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireCreate;
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
test('cannot create a site record without being authenticated', function () {
    logout();

    get(route('archiving.register.site.create'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access site record creation route', function () {
    get(route('archiving.register.site.create'))
    ->assertForbidden();
});

test('cannot render site record creation component without specific permission', function () {
    Livewire::test(SiteLivewireCreate::class)
    ->assertForbidden();
});

test('cannot set the site record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    $site = Site::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->assertOk()
    ->call('markToDelete', $site->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the site record which will be deleted if it has buildings', function () {
    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    Building::factory()
    ->for($site, 'site')
    ->create();

    Livewire::test(SiteLivewireCreate::class)
    ->assertOk()
    ->call('markToDelete', $site->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a site record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    $component = Livewire::test(SiteLivewireCreate::class)
    ->call('markToDelete', $site->id)
    ->assertOk();

    revokePermission(PermissionType::SiteDelete->value);

    $component->call('destroy')
    ->assertForbidden();

    expect(Site::where('id', $site->id)->exists())->toBeTrue();
});

test('cannot delete a site record if it has buildings', function () {
    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    $component = Livewire::test(SiteLivewireCreate::class)
    ->call('markToDelete', $site->id)
    ->assertOk();

    Building::factory()
    ->for($site, 'site')
    ->create();

    $component->call('destroy')
    ->assertForbidden();

    expect(Site::where('id', $site->id)->exists())->toBeTrue();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

test('name is required', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', '')
    ->call('store')
    ->assertHasErrors(['site.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', ['foo'])
    ->call('store')
    ->assertHasErrors(['site.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', Str::random(101))
    ->call('store')
    ->assertHasErrors(['site.name' => 'max']);
});

test('name must be unique', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.description', '')
    ->call('store')
    ->assertHasNoErrors(['site.description']);
});

test('description must be an string', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.description', ['foo'])
    ->call('store')
    ->assertHasErrors(['site.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.description', Str::random(256))
    ->call('store')
    ->assertHasErrors(['site.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected site records', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Site::factory(120)->create();

    Livewire::test(SiteLivewireCreate::class)
    ->assertCount('sites', 10)
    ->set('per_page', 10)
    ->assertCount('sites', 10)
    ->set('per_page', 25)
    ->assertCount('sites', 25)
    ->set('per_page', 50)
    ->assertCount('sites', 50)
    ->set('per_page', 100)
    ->assertCount('sites', 100);
});

test('pagination creates the session variables', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
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

test('renders site record creation component with specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    get(route('archiving.register.site.create'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireCreate::class);
});

test('emits feedback event when creates a site record', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a site record', function () {
    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->call('markToDelete', $site->id)
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

test('creates a site record with specific permission', function () {
    grantPermission(PermissionType::SiteCreate->value);

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->set('site.description', 'foo bar')
    ->call('store')
    ->assertOk();

    $site = Site::first();

    expect($site->name)->toBe('foo')
    ->and($site->description)->toBe('foo bar');
});

test('reset to a blank model after the site is created', function () {
    grantPermission(PermissionType::SiteCreate->value);

    $blank = new Site();

    Livewire::test(SiteLivewireCreate::class)
    ->set('site.name', 'foo')
    ->call('store')
    ->assertOk()
    ->assertSet('site', $blank);
});

test('defines the site record that will be deleted with specific permission and if it has no buildings', function () {
    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->call('markToDelete', $site->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $site->id);
});

test('delete a site record with specific permission if it has no buildings', function () {
    grantPermission(PermissionType::SiteCreate->value);
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();

    Livewire::test(SiteLivewireCreate::class)
    ->call('markToDelete', $site->id)
    ->assertOk()
    ->call('destroy', $site->id)
    ->assertOk();

    expect(Site::where('id', $site->id)->doesntExist())->toBeTrue();
});
