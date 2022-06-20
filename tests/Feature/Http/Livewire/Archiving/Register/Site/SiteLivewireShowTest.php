<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Http\Livewire\Archiving\Register\Site\SiteLivewireShow;
use App\Models\Building;
use App\Models\Site;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
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
test('cannot individually view a site without being authenticated', function () {
    logout();

    get(route('archiving.register.site.show', $this->site))
    ->assertRedirect(route('login'));
});

test('authenticated but without specific permission, unable to access individual site view route', function () {
    get(route('archiving.register.site.show', $this->site))
    ->assertForbidden();
});

test('cannot render individual site view component without specific permission', function () {
    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->assertForbidden();
});

// Rules
test('does not accept pagination outside the options offered', function () {
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
    ->set('per_page', 33) // possible values: 10/25/50/100
    ->assertHasErrors(['per_page' => 'in']);
});

// Happy path
test('renders individual site view component with specific permission', function () {
    grantPermission(PermissionType::SiteView->value);

    get(route('archiving.register.site.show', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireShow::class);
});

test('pagination returns the amount of buildinds expected', function () {
    grantPermission(PermissionType::SiteView->value);

    Building::factory(120)->for($this->site, 'site')->create();

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
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
    grantPermission(PermissionType::SiteView->value);

    Livewire::test(SiteLivewireShow::class, ['site' => $this->site])
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

test('individually view a site with specific permission', function () {
    grantPermission(PermissionType::SiteView->value);

    get(route('archiving.register.site.show', $this->site))
    ->assertOk()
    ->assertSeeLivewire(SiteLivewireShow::class);
});

test('SiteLivewireShow uses the withsorting trait', function () {
    expect(
        collect(class_uses(SiteLivewireShow::class))
        ->contains(\App\Http\Livewire\Traits\WithSorting::class)
    )->toBeTrue();
});
