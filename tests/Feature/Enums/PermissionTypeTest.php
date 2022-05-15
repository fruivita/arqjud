<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;

// Happy path
test('permissions ids for configurations administration are set', function () {
    expect(PermissionType::ConfigurationView->value)->toBe(100002)
    ->and(PermissionType::ConfigurationUpdate->value)->toBe(100004);
});

test('permissions ids for delegation are set', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(110001)
    ->and(PermissionType::DelegationCreate->value)->toBe(110003);
});

test('permissions ids for application documentation administration are set', function () {
    expect(PermissionType::DocumentationViewAny->value)->toBe(120001)
    ->and(PermissionType::DocumentationCreate->value)->toBe(120003)
    ->and(PermissionType::DocumentationUpdate->value)->toBe(120004)
    ->and(PermissionType::DocumentationDelete->value)->toBe(120006);
});

test('permissions ids to importing usage data are set', function () {
    expect(PermissionType::ImportationCreate->value)->toBe(130003);
});

test('permissions ids for application logs administration are set', function () {
    expect(PermissionType::LogViewAny->value)->toBe(140001)
    ->and(PermissionType::LogDelete->value)->toBe(140006)
    ->and(PermissionType::LogDownload->value)->toBe(140101);
});

test('permissions ids for permissions administration are set', function () {
    expect(PermissionType::PermissionViewAny->value)->toBe(150001)
    ->and(PermissionType::PermissionView->value)->toBe(150002)
    ->and(PermissionType::PermissionUpdate->value)->toBe(150004);
});

test('permissions ids for roles administration are set', function () {
    expect(PermissionType::RoleViewAny->value)->toBe(160001)
    ->and(PermissionType::RoleView->value)->toBe(160002)
    ->and(PermissionType::RoleUpdate->value)->toBe(160004);
});

test('permissions id to create a usage simulation are set', function () {
    expect(PermissionType::SimulationCreate->value)->toBe(170003);
});

test('permissions ids for users administration are set', function () {
    expect(PermissionType::UserViewAny->value)->toBe(180001)
    ->and(PermissionType::UserUpdate->value)->toBe(180004);
});
