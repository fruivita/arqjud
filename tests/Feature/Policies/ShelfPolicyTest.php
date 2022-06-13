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

beforeEach(function () {
    $this->seed([DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('foo');
});

afterEach(function () {
    logout();
});

// Forbidden
test('user without permission cannot list shelves records', function () {
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
test('user with permission can list shelves records', function () {
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
