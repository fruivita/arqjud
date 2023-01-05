<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Policy;

// Caminho feliz
test('Policy enum corretamente definidos', function () {
    expect(Policy::ViewAny->value)->toBe('viewAny')
        ->and(Policy::View->value)->toBe('view')
        ->and(Policy::Create->value)->toBe('create')
        ->and(Policy::Update->value)->toBe('update')
        ->and(Policy::Delete->value)->toBe('delete')
        ->and(Policy::Restore->value)->toBe('restore')
        ->and(Policy::ForceDelete->value)->toBe('forceDelete')
        ->and(Policy::ViewAnyOrUpdate->value)->toBe('viewAnyOrUpdate')
        ->and(Policy::ViewOrUpdate->value)->toBe('viewOrUpdate')
        ->and(Policy::ExternoViewAny->value)->toBe('externoViewAny')
        ->and(Policy::ExternoCreate->value)->toBe('externoCreate')
        ->and(Policy::ExternoDelete->value)->toBe('externoDelete')
        ->and(Policy::ImportacaoCreate->value)->toBe('importacaoCreate')
        ->and(Policy::LogViewAny->value)->toBe('logViewAny')
        ->and(Policy::LogView->value)->toBe('logView')
        ->and(Policy::LogDelete->value)->toBe('logDelete')
        ->and(Policy::MoverProcessoCreate->value)->toBe('moverProcessoCreate');
});
