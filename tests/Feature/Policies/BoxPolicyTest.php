<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\BoxPolicy;
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
test('user without permission cannot list boxes records', function () {
    expect((new BoxPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a box', function () {
    expect((new BoxPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot create a box', function () {
    expect((new BoxPolicy())->create($this->user))->toBeFalse();
});

test('user without permission cannot create multiple boxes', function () {
    expect((new BoxPolicy())->createMany($this->user))->toBeFalse();
});

test('user without permission cannot update a box', function () {
    expect((new BoxPolicy())->update($this->user))->toBeFalse();
});

test('user without permission cannot delete a box', function () {
    expect((new BoxPolicy())->delete($this->user))->toBeFalse();
});

// Happy path
test('user with permission can list boxes records', function () {
    grantPermission(PermissionType::BoxViewAny->value);

    expect((new BoxPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a box', function () {
    grantPermission(PermissionType::BoxView->value);

    expect((new BoxPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can create a box', function () {
    grantPermission(PermissionType::BoxCreate->value);

    expect((new BoxPolicy())->create($this->user))->toBeTrue();
});

test('user with permission can create multiple boxes', function () {
    grantPermission(PermissionType::BoxCreateMany->value);

    expect((new BoxPolicy())->createMany($this->user))->toBeTrue();
});

test('user with permission can individually update a box', function () {
    grantPermission(PermissionType::BoxUpdate->value);

    expect((new BoxPolicy())->update($this->user))->toBeTrue();
});
