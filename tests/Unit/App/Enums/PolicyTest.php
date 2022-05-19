<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;

// Happy path
test('Policy enum correctly defined', function () {
    expect(Policy::ViewAny->value)->toBe('view-any')
    ->and(Policy::View->value)->toBe('view')
    ->and(Policy::Create->value)->toBe('create')
    ->and(Policy::Update->value)->toBe('update')
    ->and(Policy::Delete->value)->toBe('delete')
    ->and(Policy::Restore->value)->toBe('restore')
    ->and(Policy::ForceDelete->value)->toBe('force-delete')
    ->and(Policy::DelegationViewAny->value)->toBe('delegation-view-any')
    ->and(Policy::DelegationCreate->value)->toBe('delegation-create')
    ->and(Policy::DelegationDelete->value)->toBe('delegation-delete')
    ->and(Policy::ImportationCreate->value)->toBe('importation-create')
    ->and(Policy::LogViewAny->value)->toBe('log-view-any')
    ->and(Policy::LogDelete->value)->toBe('log-delete')
    ->and(Policy::LogDownload->value)->toBe('log-download')
    ->and(Policy::SimulationCreate->value)->toBe('simulation-create')
    ->and(Policy::SimulationDelete->value)->toBe('simulation-delete');
});
