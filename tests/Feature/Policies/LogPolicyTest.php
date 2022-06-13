<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\LogPolicy;
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
test('user without permission cannot list application logs', function () {
    expect((new LogPolicy())->viewAny($this->user))->toBeFalse();
});

test('user without permission cannot delete application logs', function () {
    expect((new LogPolicy())->delete($this->user))->toBeFalse();
});

test('user without permission cannot download application logs', function () {
    expect((new LogPolicy())->download($this->user))->toBeFalse();
});

// Happy path
test('user with permission can list application logs', function () {
    grantPermission(PermissionType::LogViewAny->value);

    expect((new LogPolicy())->viewAny($this->user))->toBeTrue();
});

test('user with permission can individually delete an application log', function () {
    grantPermission(PermissionType::LogDelete->value);

    expect((new LogPolicy())->delete($this->user))->toBeTrue();
});

test('user with permission can download individual application log', function () {
    grantPermission(PermissionType::LogDownload->value);

    expect((new LogPolicy())->download($this->user))->toBeTrue();
});
