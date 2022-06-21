<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireIndex;
use App\Models\Building;
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
test('cannot list site records without being authenticated', function () {
    logout();

    get(route('archiving.register.site.index'))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, cannot access site records listing route', function () {
    get(route('archiving.register.site.index'))
    ->assertForbidden();
});

test('cannot render listing component from site records without specific permission', function () {
    Livewire::test(SiteLivewireIndex::class)->assertForbidden();
});

test('cannot set the site record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Livewire::test(SiteLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->site->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot set the site record which will be deleted if it has buildings', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    Building::factory()->for($this->site, 'site')->create();

    Livewire::test(SiteLivewireIndex::class)
    ->assertOk()
    ->call('setToDelete', $this->site->id)
    ->assertForbidden()
    ->assertSet('show_delete_modal', false)
    ->assertSet('deleting', null);
});

test('cannot delete a site record without specific permission', function () {
    \Spatie\Once\Cache::getInstance()->disable();

    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    $component = Livewire::test(SiteLivewireIndex::class)
    ->call('setToDelete', $this->site->id)
    ->assertOk();

    revokePermission(PermissionType::SiteDelete->value);

    $component->call('destroy')
    ->assertForbidden();

    expect(Site::where('id', $this->site->id)->exists())->toBeTrue();
});

test('cannot delete a site record if it has buildings', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    $component = Livewire::test(SiteLivewireIndex::class)
    ->call('setToDelete', $this->site->id)
    ->assertOk();

    Building::factory()->for($this->site, 'site')->create();

    $component->call('destroy')
    ->assertForbidden();

    expect(Site::where('id', $this->site->id)->exists())->toBeTrue();
});

// Happy path
test('pagination returns the amount of expected site records', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Site::factory(30)->create();

    Livewire::test(SiteLivewireIndex::class)
    ->set('per_page', 25)
    ->assertCount('sites', 25);
});

test('lists site records with specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    get(route('archiving.register.site.index'))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireIndex::class);
});

test('search returns expected results', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    $this->site->delete();

    Site::factory()->create(['name' => 'foo']);
    Site::factory()->create(['name' => 'bar baz']); // contains bar
    Site::factory()->create(['name' => 'bar']);

    Livewire::test(SiteLivewireIndex::class)
    ->set('term', 'foo')
    ->assertCount('sites', 1)
    ->set('term', 'bar')
    ->assertCount('sites', 2)
    ->set('term', '')
    ->assertCount('sites', 3);
});

test('emits feedback event when deleting a site record', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    Livewire::test(SiteLivewireIndex::class)
    ->call('setToDelete', $this->site->id)
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

test('defines the site record that will be deleted with specific permission and if it has no buildings', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    Livewire::test(SiteLivewireIndex::class)
    ->call('setToDelete', $this->site->id)
    ->assertOk()
    ->assertSet('show_delete_modal', true)
    ->assertSet('deleting.id', $this->site->id);
});

test('delete a site record with specific permission if it has no buildings', function () {
    grantPermission(PermissionType::SiteViewAny->value);
    grantPermission(PermissionType::SiteDelete->value);

    expect(Site::where('id', $this->site->id)->exists())->toBeTrue();

    Livewire::test(SiteLivewireIndex::class)
    ->call('setToDelete', $this->site->id)
    ->assertOk()
    ->call('destroy', $this->site->id)
    ->assertOk();

    expect(Site::where('id', $this->site->id)->doesntExist())->toBeTrue();
});

test('SiteLivewireIndex uses trait', function () {
    expect(
        collect(class_uses(SiteLivewireIndex::class))
        ->has([
            \App\Http\Livewire\Traits\WithSorting::class,
        ])
    )->toBeTrue();
});
