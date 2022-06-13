<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;
use App\Models\Permission;
use Database\Seeders\ConfigurationSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed([ConfigurationSeeder::class, DepartmentSeeder::class, RoleSeeder::class]);

    $this->user = login('dumb user');

    $this->user->refresh();
});

afterEach(function () {
    logout();
});

// Happy path
test('super admin bypasses permission checks even without having any permissions', function () {
    expect($this->user->role->permissions)->toBeEmpty()
    ->and($this->user->can(Policy::Update, Permission::class))->toBeTrue();
});
