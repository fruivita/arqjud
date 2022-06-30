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

test('permissions ids for box volumes administration are set', function () {
    expect(PermissionType::BoxVolumeViewAny->value)->toBe(110001)
    ->and(PermissionType::BoxVolumeView->value)->toBe(110002)
    ->and(PermissionType::BoxVolumeCreate->value)->toBe(110003)
    ->and(PermissionType::BoxVolumeUpdate->value)->toBe(110004)
    ->and(PermissionType::BoxVolumeDelete->value)->toBe(110005);
});

test('permissions ids for buildings administration are set', function () {
    expect(PermissionType::BuildingViewAny->value)->toBe(120001)
    ->and(PermissionType::BuildingView->value)->toBe(120002)
    ->and(PermissionType::BuildingCreate->value)->toBe(120003)
    ->and(PermissionType::BuildingUpdate->value)->toBe(120004)
    ->and(PermissionType::BuildingDelete->value)->toBe(120005);
});

test('permissions ids for configurations administration are set', function () {
    expect(PermissionType::ConfigurationView->value)->toBe(130002)
    ->and(PermissionType::ConfigurationUpdate->value)->toBe(130004);
});

test('permissions ids for delegation are set', function () {
    expect(PermissionType::DelegationViewAny->value)->toBe(140001)
    ->and(PermissionType::DelegationCreate->value)->toBe(140003);
});

test('permissions ids for application documentation administration are set', function () {
    expect(PermissionType::DocumentationViewAny->value)->toBe(150001)
    ->and(PermissionType::DocumentationView->value)->toBe(150002)
    ->and(PermissionType::DocumentationCreate->value)->toBe(150003)
    ->and(PermissionType::DocumentationUpdate->value)->toBe(150004)
    ->and(PermissionType::DocumentationDelete->value)->toBe(150005);
});

test('permissions ids for floors administration are set', function () {
    expect(PermissionType::FloorViewAny->value)->toBe(160001)
    ->and(PermissionType::FloorView->value)->toBe(160002)
    ->and(PermissionType::FloorCreate->value)->toBe(160003)
    ->and(PermissionType::FloorUpdate->value)->toBe(160004)
    ->and(PermissionType::FloorDelete->value)->toBe(160005);
});

test('permissions ids to importing usage data are set', function () {
    expect(PermissionType::ImportationCreate->value)->toBe(170003);
});

test('permissions ids for application logs administration are set', function () {
    expect(PermissionType::LogViewAny->value)->toBe(180001)
    ->and(PermissionType::LogDelete->value)->toBe(180005)
    ->and(PermissionType::LogDownload->value)->toBe(180101);
});

test('permissions ids for permissions administration are set', function () {
    expect(PermissionType::PermissionViewAny->value)->toBe(190001)
    ->and(PermissionType::PermissionView->value)->toBe(190002)
    ->and(PermissionType::PermissionUpdate->value)->toBe(190004);
});

test('permissions ids for processes administration are set', function () {
    expect(PermissionType::ProcessViewAny->value)->toBe(200001)
    ->and(PermissionType::ProcessView->value)->toBe(200002)
    ->and(PermissionType::ProcessCreate->value)->toBe(200003)
    ->and(PermissionType::ProcessUpdate->value)->toBe(200004);
});

test('permissions ids for roles administration are set', function () {
    expect(PermissionType::RoleViewAny->value)->toBe(210001)
    ->and(PermissionType::RoleView->value)->toBe(210002)
    ->and(PermissionType::RoleUpdate->value)->toBe(210004);
});

test('permissions ids for rooms administration are set', function () {
    expect(PermissionType::RoomViewAny->value)->toBe(220001)
    ->and(PermissionType::RoomView->value)->toBe(220002)
    ->and(PermissionType::RoomCreate->value)->toBe(220003)
    ->and(PermissionType::RoomUpdate->value)->toBe(220004)
    ->and(PermissionType::RoomDelete->value)->toBe(220005);
});

test('permissions id to create a usage simulation are set', function () {
    expect(PermissionType::SimulationCreate->value)->toBe(230003);
});

test('permissions ids for shelves administration are set', function () {
    expect(PermissionType::ShelfViewAny->value)->toBe(240001)
    ->and(PermissionType::ShelfView->value)->toBe(240002)
    ->and(PermissionType::ShelfCreate->value)->toBe(240003)
    ->and(PermissionType::ShelfUpdate->value)->toBe(240004)
    ->and(PermissionType::ShelfDelete->value)->toBe(240005);
});

test('permissions ids for sites administration are set', function () {
    expect(PermissionType::SiteViewAny->value)->toBe(250001)
    ->and(PermissionType::SiteView->value)->toBe(250002)
    ->and(PermissionType::SiteCreate->value)->toBe(250003)
    ->and(PermissionType::SiteUpdate->value)->toBe(250004)
    ->and(PermissionType::SiteDelete->value)->toBe(250005);
});

test('permissions ids for stands administration are set', function () {
    expect(PermissionType::StandViewAny->value)->toBe(260001)
    ->and(PermissionType::StandView->value)->toBe(260002)
    ->and(PermissionType::StandCreate->value)->toBe(260003)
    ->and(PermissionType::StandUpdate->value)->toBe(260004)
    ->and(PermissionType::StandDelete->value)->toBe(260005);
});

test('permissions ids for users administration are set', function () {
    expect(PermissionType::UserViewAny->value)->toBe(270001)
    ->and(PermissionType::UserUpdate->value)->toBe(270004);
});
