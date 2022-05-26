<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\PermissionType;

// Happy path
test('permissions ids for boxes administration are set', function () {
    expect(PermissionType::BoxViewAny->value)->toBe(100001)
    ->and(PermissionType::BoxView->value)->toBe(100002)
    ->and(PermissionType::BoxCreate->value)->toBe(100003)
    ->and(PermissionType::BoxCreateMany->value)->toBe(100101)
    ->and(PermissionType::BoxUpdate->value)->toBe(100004)
    ->and(PermissionType::BoxDelete->value)->toBe(100005);
});

test('permissions ids for configurations administration are set', function () {
    expect(PermissionType::ConfigurationView->value)->toBe(110002)
    ->and(PermissionType::ConfigurationUpdate->value)->toBe(110004);
});

test('permissions ids for delegation are set', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(120001)
    ->and(PermissionType::DelegationCreate->value)->toBe(120003);
});

test('permissions ids for application documentation administration are set', function () {
    expect(PermissionType::DocumentationViewAny->value)->toBe(130001)
    ->and(PermissionType::DocumentationCreate->value)->toBe(130003)
    ->and(PermissionType::DocumentationUpdate->value)->toBe(130004)
    ->and(PermissionType::DocumentationDelete->value)->toBe(130005);
});

test('permissions ids to importing usage data are set', function () {
    expect(PermissionType::ImportationCreate->value)->toBe(140003);
});

test('permissions ids for application logs administration are set', function () {
    expect(PermissionType::LogViewAny->value)->toBe(150001)
    ->and(PermissionType::LogDelete->value)->toBe(150005)
    ->and(PermissionType::LogDownload->value)->toBe(150101);
});

test('permissions ids for permissions administration are set', function () {
    expect(PermissionType::PermissionViewAny->value)->toBe(160001)
    ->and(PermissionType::PermissionView->value)->toBe(160002)
    ->and(PermissionType::PermissionUpdate->value)->toBe(160004);
});

test('permissions ids for processes administration are set', function () {
    expect(PermissionType::ProcessViewAny->value)->toBe(170001)
    ->and(PermissionType::ProcessView->value)->toBe(170002)
    ->and(PermissionType::ProcessCreate->value)->toBe(170003)
    ->and(PermissionType::ProcessUpdate->value)->toBe(170004);
});

test('permissions ids for roles administration are set', function () {
    expect(PermissionType::RoleViewAny->value)->toBe(180001)
    ->and(PermissionType::RoleView->value)->toBe(180002)
    ->and(PermissionType::RoleUpdate->value)->toBe(180004);
});

test('permissions id to create a usage simulation are set', function () {
    expect(PermissionType::SimulationCreate->value)->toBe(190003);
});

test('permissions ids for users administration are set', function () {
    expect(PermissionType::UserViewAny->value)->toBe(200001)
    ->and(PermissionType::UserUpdate->value)->toBe(200004);
});
