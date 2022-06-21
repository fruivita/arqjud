<?php

/**
 * @see https://pestphp.com/docs/
 */

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
// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Livewire::test(SiteLivewireIndex::class)
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('pagination returns the amount of expected site records', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    Site::factory(120)->create();

    Livewire::test(SiteLivewireIndex::class)
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
    grantPermission(PermissionType::SiteViewAny->value);

    Livewire::test(SiteLivewireIndex::class)
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
