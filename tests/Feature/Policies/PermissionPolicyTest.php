<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\PermissionPolicy;
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
test('user without permission cannot list permissions', function () {
    expect((new PermissionPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot individually view a permission', function () {
    expect((new PermissionPolicy())->view($this->user))->toBeFalse();
});

test('user without permission cannot update a permission', function () {
    expect((new PermissionPolicy())->update($this->user))->toBeFalse();
});

// Happy path
test('user with permission can list permissions', function () {
    grantPermission(PermissionType::PermissionViewAny->value);

    expect((new PermissionPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually view a permission', function () {
    grantPermission(PermissionType::PermissionView->value);

    expect((new PermissionPolicy())->view($this->user))->toBeTrue();
});

test('user with permission can individually update a permission', function () {
    grantPermission(PermissionType::PermissionUpdate->value);

    expect((new PermissionPolicy())->update($this->user))->toBeTrue();
});
