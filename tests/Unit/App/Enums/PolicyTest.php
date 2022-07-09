<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;

// Caminho feliz
test('Policy enum corretamente definidos', function () {
    expect(Policy::ViewAny->value)->toBe('view-any')
    ->and(Policy::View->value)->toBe('view')
    ->and(Policy::Create->value)->toBe('create')
    ->and(Policy::CreateMany->value)->toBe('create-many')
    ->and(Policy::Update->value)->toBe('update')
    ->and(Policy::Delete->value)->toBe('delete')
    ->and(Policy::Restore->value)->toBe('restore')
    ->and(Policy::ForceDelete->value)->toBe('force-delete')
    ->and(Policy::DelegacaoViewAny->value)->toBe('delegacao-view-any')
    ->and(Policy::DelegacaoCreate->value)->toBe('delegacao-create')
    ->and(Policy::DelegacaoDelete->value)->toBe('delegacao-delete')
    ->and(Policy::ImportacaoCreate->value)->toBe('importacao-create')
    ->and(Policy::LogViewAny->value)->toBe('log-view-any')
    ->and(Policy::LogDelete->value)->toBe('log-delete')
    ->and(Policy::LogDownload->value)->toBe('log-download')
    ->and(Policy::SimulacaoCreate->value)->toBe('simulacao-create')
    ->and(Policy::SimulacaoDelete->value)->toBe('simulacao-delete')
    ->and(Policy::ViewAnyOrUpdate->value)->toBe('view-any-or-update')
    ->and(Policy::ViewOrUpdate->value)->toBe('view-or-update');
});
