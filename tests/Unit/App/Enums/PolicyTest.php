<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;

// Caminho feliz
test('Policy enum corretamente definidos', function () {
    expect(Policy::ViewAny->value)->toBe('view_any')
        ->and(Policy::View->value)->toBe('view')
        ->and(Policy::Create->value)->toBe('create')
        ->and(Policy::Update->value)->toBe('update')
        ->and(Policy::Delete->value)->toBe('delete')
        ->and(Policy::Restore->value)->toBe('restore')
        ->and(Policy::ForceDelete->value)->toBe('force_delete')
        ->and(Policy::ViewAnyOrUpdate->value)->toBe('view_any_or_update')
        ->and(Policy::ViewOrUpdate->value)->toBe('view_or_update')
        ->and(Policy::ExternoViewAny->value)->toBe('externo_view_any')
        ->and(Policy::ExternoCreate->value)->toBe('externo_create')
        ->and(Policy::ExternoDelete->value)->toBe('externo_delete')
        ->and(Policy::DelegacaoViewAny->value)->toBe('delegacao_view_any')
        ->and(Policy::DelegacaoCreate->value)->toBe('delegacao_create')
        ->and(Policy::DelegacaoDelete->value)->toBe('delegacao_delete')
        ->and(Policy::ImportacaoCreate->value)->toBe('importacao_create')
        ->and(Policy::LogViewAny->value)->toBe('log_view_any')
        ->and(Policy::LogView->value)->toBe('log_view')
        ->and(Policy::LogDelete->value)->toBe('log_delete')
        ->and(Policy::MoverProcessoCreate->value)->toBe('mover_processo_create');
});
