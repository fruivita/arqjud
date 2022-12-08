<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Search;
use App\Models\Localidade;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('não aplica o escopo search se a chave termo for inexistente no request de acordo com o snapshot', function () {
    request()->merge(['termo' => '']);

    $query = Pipeline::make()
        ->send(Localidade::query())
        ->through([Search::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});

test('aplica o escopo search se a chave termo existir no request de acordo com o snapshot', function () {
    request()->merge(['termo' => 'foo']);

    $query = Pipeline::make()
        ->send(Localidade::query())
        ->through([Search::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
