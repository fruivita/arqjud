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

test('cannot update site if edit mode is disabled', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

test('cannot update site without specific permission', function () {
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', false)
    ->call('update')
    ->assertForbidden();
});

// Rules
test('name is required', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.name', '')
    ->call('update')
    ->assertHasErrors(['site.name' => 'required']);
});

test('name must be a string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.name', ['foo'])
    ->call('update')
    ->assertHasErrors(['site.name' => 'string']);
});

test('name must be a maximum of 100 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.name', Str::random(101))
    ->call('update')
    ->assertHasErrors(['site.name' => 'max']);
});

test('name must be unique', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Site::factory()->create(['name' => 'foo']);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.name', 'foo')
    ->call('update')
    ->assertHasErrors(['site.name' => 'unique']);
});

test('description is optional', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.description', '')
    ->call('update')
    ->assertHasNoErrors(['site.description']);
});

test('description must be an string', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.description', ['foo'])
    ->call('update')
    ->assertHasErrors(['site.description' => 'string']);
});

test('description must be a maximum of 255 characters', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.description', Str::random(256))
    ->call('update')
    ->assertHasErrors(['site.description' => 'max']);
});

// Happy path
test('pagination returns the amount of expected building records', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Building::factory(30)->for($this->site, 'site')->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('per_page', 25)
    ->assertCount('buildings', 25);
});

test('renders edit site record component with view or update permission', function ($permission) {
    grantPermission($permission);

    get(route('archiving.register.site.edit', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireUpdate::class);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->assertOk();
})->with([
    PermissionType::SiteView->value,
    PermissionType::SiteUpdate->value
]);

test('emits feedback event when update a site record', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.name', 'foo')
    ->call('update')
    ->assertEmitted('feedback', FeedbackType::Success, __('Success!'));
});

test('emits feedback event when deleting a building record', function () {
    grantPermission(PermissionType::SiteUpdate->value);
    grantPermission(PermissionType::BuildingDelete->value);

    $building = Building::factory()->for($this->site, 'site')->create();

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->call('setToDelete', $building->id)
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

    Livewire::test(SiteLivewireUpdate::class, ['site' => $this->site])
    ->set('modo_edicao', true)
    ->set('site.name', 'foo')
    ->set('site.description', 'foo bar')
    ->call('update')
    ->assertOk();

    $this->site->refresh();

    expect($this->site->name)->toBe('foo')
    ->and($this->site->description)->toBe('foo bar');
});

test('SiteLivewireUpdate uses trait', function () {
    expect(
        collect(class_uses(SiteLivewireUpdate::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
