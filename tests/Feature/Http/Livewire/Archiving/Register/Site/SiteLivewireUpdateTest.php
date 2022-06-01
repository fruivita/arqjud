<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireUpdate;
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
test('cannot update a site record without being authenticated', function () {
    logout();

    get(route('archiving.register.site.edit', $this->site))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access site record edit route', function () {
    get(route('archiving.register.site.edit', $this->site))
    ->assertForbidden();
});

test('cannot render site record edit component without specific permission', function () {
    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->assertForbidden();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', '')
    ->call('update')
    ->assertHasErrors(['site.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', ['foo'])
    ->call('update')
    ->assertHasErrors(['site.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', Str::random(101))
    ->call('update')
    ->assertHasErrors(['site.name' => 'max']);
});

test('name must be unique', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', 'foo')
    ->call('update')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.description', '')
    ->call('update')
    ->assertHasNoErrors(['site.description']);
});

test('description must be an string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['site.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['site.description' => 'max']);
});

// Happy path
test('renders edit site record component with specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    get(route('archiving.register.site.edit', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireUpdate::class);
});

test('emits feedback event when update a site record', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', 'foo')
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('update a site record with specific permission', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('site.name', 'foo')
    ->set('site.description', 'foo bar')
    ->call('update')
    ->assertOk();

    $this->site->refresh();

    expect($this->site->name)->toBe('foo')
    ->and($this->site->description)->toBe('foo bar');
});
