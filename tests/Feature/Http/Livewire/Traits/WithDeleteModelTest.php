<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\FeedbackType;
use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireIndex;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->site = Site::factory()->create();

    login('foo');
});

afterEach(function () {
    logout();
});

// Authorization
test('cannot set the site record which will be deleted without specific permission', function () {
    grantPermission(PermissionType::SiteViewAny->value);

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

// Happy path
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
