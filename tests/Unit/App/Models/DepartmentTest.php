<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Department;
use Database\Seeders\DepartmentSeeder;

beforeEach(function () {
    $this->seed(DepartmentSeeder::class);
});

// Happy path
test('default department ids for users with no department is set', function () {
    expect(Department::DEPARTMENTLESS)->toBe(0);
});
