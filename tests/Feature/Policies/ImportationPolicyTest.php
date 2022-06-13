<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;
use App\Policies\ImportationPolicy;
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
test('user without permission cannot perform an import', function () {
    expect((new ImportationPolicy())->create($this->user))->toBeFalse();
});

// Happy path
test('user with permission can perform an import', function () {
    grantPermission(PermissionType::ImportationCreate->value);

    expect((new ImportationPolicy())->create($this->user))->toBeTrue();
});
