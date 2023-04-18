<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Atividade;
use Spatie\Activitylog\Contracts\Activity;

// Caminho feliz
test('retorna as atividades pelo escopo search que busca a partir do início do log name', function () {
    activity('foo')->log('log');
    activity('bar')->log('log');
    activity('baz')->log('log');
    activity('taz')->log('log');

    expect(Atividade::search()->count())->toBe(4)
        ->and(Atividade::search('ta')->count())->toBe(1)
        ->and(Atividade::search('ba')->count())->toBe(2)
        ->and(Atividade::search('oo')->count())->toBe(0);
});

test('retorna as atividades pelo escopo search que busca a partir do início da description', function () {
    activity()->log('foo');
    activity()->log('bar');
    activity()->log('baz');
    activity()->log('taz');

    expect(Atividade::search()->count())->toBe(4)
        ->and(Atividade::search('ta')->count())->toBe(1)
        ->and(Atividade::search('ba')->count())->toBe(2)
        ->and(Atividade::search('oo')->count())->toBe(0);
});

test('retorna as atividades pelo escopo search que busca a partir do início da event', function () {
    activity()->event('foo')->log('a');
    activity()->event('bar')->log('a');
    activity()->event('baz')->log('a');
    activity()->event('taz')->log('a');

    expect(Atividade::search()->count())->toBe(4)
        ->and(Atividade::search('ta')->count())->toBe(1)
        ->and(Atividade::search('ba')->count())->toBe(2)
        ->and(Atividade::search('oo')->count())->toBe(0);
});

test('retorna as atividades pelo escopo search que busca a partir do início da matrícula', function () {
    activity()->tap(fn (Activity $atv) => $atv->matricula = 'foo')->log('a');
    activity()->tap(fn (Activity $atv) => $atv->matricula = 'bar')->log('a');
    activity()->tap(fn (Activity $atv) => $atv->matricula = 'baz')->log('a');
    activity()->tap(fn (Activity $atv) => $atv->matricula = 'taz')->log('a');

    expect(Atividade::search()->count())->toBe(4)
        ->and(Atividade::search('ta')->count())->toBe(1)
        ->and(Atividade::search('ba')->count())->toBe(2)
        ->and(Atividade::search('oo')->count())->toBe(0);
});

test('retorna as atividades pelo escopo search que busca a partir do início do batch', function () {
    activity()->tap(fn (Activity $atv) => $atv->batch_uuid = 'foo')->log('a');
    activity()->tap(fn (Activity $atv) => $atv->batch_uuid = 'bar')->log('a');
    activity()->tap(fn (Activity $atv) => $atv->batch_uuid = 'baz')->log('a');
    activity()->tap(fn (Activity $atv) => $atv->batch_uuid = 'taz')->log('a');

    expect(Atividade::search()->count())->toBe(4)
        ->and(Atividade::search('ta')->count())->toBe(1)
        ->and(Atividade::search('ba')->count())->toBe(2)
        ->and(Atividade::search('oo')->count())->toBe(0);
});
