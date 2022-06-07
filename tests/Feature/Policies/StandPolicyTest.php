<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Shelf;
use App\Models\Stand;
use App\Policies\StandPolicy;
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
test('user without permission cannot list stands records', function () {
    expect((new StandPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a stand', function () {
    expect((new StandPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a stand', function () {
    expect((new StandPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a stand', function () {
    expect((new StandPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a stand', function () {
    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeFalse();
});

test('stand with shelves cannot be delete', function () {
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()
    ->has(Shelf::factory(2), 'shelves')
    ->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeFalse();
});

// Happy path
test('permission to list stands records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::StandViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new StandPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new StandPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::StandViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new StandPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new StandPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a stand is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::StandView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new StandPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new StandPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::StandView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new StandPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new StandPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a stand is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::StandCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new StandPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new StandPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::StandCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new StandPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new StandPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a stand is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::StandUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new StandPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new StandPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::StandUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new StandPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new StandPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a stand is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::StandDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new StandPolicy())->delete($this->user, $stand))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list stands records', function () {
    grantPermission(PermissionType::StandViewAny->value);

    expect((new StandPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a stand', function () {
    grantPermission(PermissionType::StandView->value);

    expect((new StandPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a stand', function () {
    grantPermission(PermissionType::StandCreate->value);

    expect((new StandPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a stand', function () {
    grantPermission(PermissionType::StandUpdate->value);

    expect((new StandPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a stand', function () {
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue();
});

test('stand without shelves can be deleted', function () {
    grantPermission(PermissionType::StandDelete->value);

    $stand = Stand::factory()->create();
    $stand->loadCount('shelves');

    expect((new StandPolicy())->delete($this->user, $stand))->toBeTrue();
});
