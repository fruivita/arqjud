<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\BoxPolicy;
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
test('user without permission cannot list documentation records', function () {
    expect((new BoxPolicy)->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a box', function () {
    expect((new BoxPolicy)->view($this->user))->toBeFalse();
});

test('user without permission cannot create a box', function () {
    expect((new BoxPolicy)->create($this->user))->toBeFalse();
});

test('user without permission cannot update a box', function () {
    expect((new BoxPolicy)->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a box', function () {
    expect((new BoxPolicy)->delete($this->user))->toBeFalse();
});

// Happy path
test('permission to list documentation records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxPolicy)->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxPolicy)->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a box is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxPolicy)->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxPolicy)->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a box is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxPolicy)->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxPolicy)->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a box is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxPolicy)->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxPolicy)->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a box is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::BoxDelete->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new BoxPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new BoxPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::BoxDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new BoxPolicy)->delete($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new BoxPolicy)->delete($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list documentation records', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    expect((new BoxPolicy)->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a box', function () {
    grantPermission(PermissionType::BoxView->value);

    expect((new BoxPolicy)->view($this->user))->toBeTrue();
});

test('user with permission can create a box', function () {
    grantPermission(PermissionType::BoxCreate->value);

    expect((new BoxPolicy)->create($this->user))->toBeTrue();
});

test('user with permission can individually update a box', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    expect((new BoxPolicy)->update($this->user))->toBeTrue();
});
