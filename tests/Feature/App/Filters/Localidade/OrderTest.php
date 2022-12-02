<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Filters\Localidade\Order;
use App\Models\Localidade;
use Illuminate\Pipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('não aplica ordenação específica se a chave order for inexistente no request de acordo com o snapshot', function () {
    request()->merge(['order' => '']);

    $query = app(Pipeline::class)
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});

test('não aplica ordenação específica se a chave order, mesmo existindo no request, não for uma chave permitida de acordo com o snapshot', function () {
    request()->merge(['order' => ['foo' => 'asc']]);

    $query = app(Pipeline::class)
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});

test('aplica a ordenação específica se a chave order no request possuir ordenação permitida para o modelo de acordo com o snapshot', function () {
    request()->merge(['order' => ['nome' => 'asc']]);

    $query = app(Pipeline::class)
        ->send(Localidade::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
