<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Atividade;
use App\Pipes\Atividade\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('sem ordenação válida no request, ordena pelo ID desc', function (string $coluna, string $direcao) {
    activity('aaa')->log('a');
    activity('bbb')->log('b');

    request()->merge(['order' => [$coluna, $direcao]]);

    $atividades = Pipeline::make()
        ->send(Atividade::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('log_name');

    expect($atividades->toArray())->toMatchArray(['bbb', 'aaa']);
})->with([
    ['', ''],
    ['foo', 'asc'],
]);

test('ordena pelo log_name', function () {
    activity('aaa')->log('a');
    activity('bbb')->log('b');

    request()->merge(['order' => ['log_name' => 'desc']]);

    $atividades = Pipeline::make()
        ->send(Atividade::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('log_name');

    expect($atividades->toArray())->toMatchArray(['bbb', 'aaa']);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Atividade::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'log_name',
    'event',
    'description',
    'subject_type',
    'subject_id',
    'causer_type',
    'causer_id',
    'matricula',
    'uuid',
    'created_at',
    'updated_at',
]);

test('com todas as ordenações específicas na atividade', function () {
    request()->merge(['order' => [
        'log_name' => 'asc',
        'event' => 'asc',
        'description' => 'asc',
        'subject_type' => 'asc',
        'subject_id' => 'asc',
        'causer_type' => 'asc',
        'causer_id' => 'desc',
        'matricula' => 'desc',
        'uuid' => 'desc',
        'created_at' => 'desc',
        'updated_at' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Atividade::query())
        ->through([Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
