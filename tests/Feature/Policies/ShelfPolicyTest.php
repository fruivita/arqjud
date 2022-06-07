<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Models\Box;
use App\Models\Shelf;
use App\Policies\ShelfPolicy;
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
test('user without permission cannot list shelfs records', function () {
    expect((new ShelfPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a shelf', function () {
    expect((new ShelfPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a shelf', function () {
    expect((new ShelfPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot update a shelf', function () {
    expect((new ShelfPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a shelf', function () {
    $shelf = Shelf::factory()->create();
    $shelf->loadCount('boxes');

    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeFalse();
});

test('shelf with boxes cannot be delete', function () {
    grantPermission(PermissionType::ShelfDelete->value);

    $shelf = Shelf::factory()
    ->has(Box::factory(2), 'boxes')
    ->create();
    $shelf->loadCount('boxes');

    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeFalse();
});

// Happy path
test('permission to list shelfs records is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ShelfViewAny->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ShelfPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ShelfPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ShelfViewAny->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ShelfPolicy())->viewAny($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ShelfPolicy())->viewAny($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually view a shelf is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ShelfView->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ShelfPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ShelfPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ShelfView->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ShelfPolicy())->view($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ShelfPolicy())->view($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to create a shelf is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ShelfCreate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ShelfPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ShelfPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ShelfCreate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ShelfPolicy())->create($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ShelfPolicy())->create($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually update a shelf is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ShelfUpdate->value);

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ShelfPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ShelfPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ShelfUpdate->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ShelfPolicy())->update($this->user))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ShelfPolicy())->update($this->user))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('permission to individually delete a shelf is cached for 5 seconds', function () {
    testTime()->freeze();
    grantPermission(PermissionType::ShelfDelete->value);

    $shelf = Shelf::factory()->create();
    $shelf->loadCount('boxes');

    $key = "{$this->user->username}-permissions";

    // no cache
    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeTrue()
    ->and(cache()->missing($key))->toBeTrue();

    // create the permissions cache when making a request
    get(route('home'));

    // with cache
    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // revoke permission and move time to expiration limit
    revokePermission(PermissionType::ShelfDelete->value);
    testTime()->addSeconds(5);

    // permission is still cached
    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeTrue()
    ->and(cache()->has($key))->toBeTrue();

    // expires cache
    testTime()->addSeconds(1);

    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeFalse()
    ->and(cache()->missing($key))->toBeTrue();
});

test('user with permission can list shelfs records', function () {
    grantPermission(PermissionType::ShelfViewAny->value);

    expect((new ShelfPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a shelf', function () {
    grantPermission(PermissionType::ShelfView->value);

    expect((new ShelfPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a shelf', function () {
    grantPermission(PermissionType::ShelfCreate->value);

    expect((new ShelfPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can individually update a shelf', function () {
    grantPermission(PermissionType::ShelfUpdate->value);

    expect((new ShelfPolicy())->update($this->user))->toBeTrue();
});

test('user with permission can individually delete a shelf', function () {
    grantPermission(PermissionType::ShelfDelete->value);

    $shelf = Shelf::factory()->create();
    $shelf->loadCount('boxes');

    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeTrue();
});

test('shelf without boxes can be deleted', function () {
    grantPermission(PermissionType::ShelfDelete->value);

    $shelf = Shelf::factory()->create();
    $shelf->loadCount('boxes');

    expect((new ShelfPolicy())->delete($this->user, $shelf))->toBeTrue();
});
