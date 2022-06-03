<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Building;
use App\Models\Site;
use App\Policies\SitePolicy;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use function Pest\Laravel\get;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('user without permission cannot list sites records', function () {
    expect((new SitePolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a site', function () {
    expect((new SitePolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a site', function () {
    expect((new SitePolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a site', function () {
    expect((new SitePolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a site', function () {
    $site = Site::factory()->create();
    $site->loadCount('buildings');

    expect((new SitePolicy())->delete($this->user, $site))->toBeFalse();
});

test('site with buildings cannot be delete', function () {
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()
    ->has(Building::factory(2), 'buildings')
    ->create();
    $site->loadCount('buildings');

    expect((new SitePolicy())->delete($this->user, $site))->toBeFalse();
});

// Happy path
test('permission to list sites records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a site is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();
    $site->loadCount('buildings');

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new SitePolicy())->delete($this->user, $site))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new SitePolicy())->delete($this->user, $site))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::SiteDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new SitePolicy())->delete($this->user, $site))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new SitePolicy())->delete($this->user, $site))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list sites records', function () {
    grantPermission(PermissionType::SiteViewAny->value);

    expect((new SitePolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a site', function () {
    grantPermission(PermissionType::SiteView->value);

    expect((new SitePolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a site', function () {
    grantPermission(PermissionType::SiteCreate->value);

    expect((new SitePolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a site', function () {
    grantPermission(PermissionType::SiteUpdate->value);

    expect((new SitePolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a site', function () {
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();
    $site->loadCount('buildings');

    expect((new SitePolicy())->delete($this->user, $site))->toBeTrue();
});

test('site without buildings can be deleted', function () {
    grantPermission(PermissionType::SiteDelete->value);

    $site = Site::factory()->create();
    $site->loadCount('buildings');

    expect((new SitePolicy())->delete($this->user, $site))->toBeTrue();
});
