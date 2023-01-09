<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Usuario;
use App\Pipes\Usuario\JoinAll;
use App\Pipes\Usuario\Order;
use MichaelRubel\EnhancedPipeline\Pipeline;
use function Spatie\Snapshots\assertMatchesSnapshot;

// Caminho feliz
test('ordena pelo nome do usuário', function () {
    Usuario::factory()->create(['id' => 1, 'nome' => 'bbbb']);
    Usuario::factory()->create(['id' => 2, 'nome' => 'aaaa']);

    request()->merge(['order' => ['nome' => 'desc']]);

    $usuarios = Pipeline::make()
        ->send(Usuario::query())
        ->through([Order::class])
        ->thenReturn()
        ->pluck('id');

    expect($usuarios->toArray())->toMatchArray([1, 2]);
});

test('todos os métodos de ordenação disponíveis são acionados', function (string $campo) {
    $this->partialMock(Order::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive(str()->camel($campo))
        ->withSomeOfArgs('desc')
        ->once();

    request()->merge(['order' => [$campo => 'desc']]);

    Pipeline::make()
        ->send(Usuario::query())
        ->through([Order::class])
        ->thenReturn();
})->with([
    'nome',
    'matricula',
    'username',
    'email',
    'ultimo_login',
    'lotacao_sigla',
    'cargo_nome',
    'funcao_nome',
    'perfil_nome',
]);

test('todas as ordenações possíveis no request do usuário', function () {
    request()->merge(['order' => [
        'nome' => 'asc',
        'matricula' => 'asc',
        'username' => 'asc',
        'email' => 'asc',
        'ultimo_login' => 'asc',
        'lotacao_sigla' => 'desc',
        'cargo_nome' => 'desc',
        'funcao_nome' => 'desc',
        'perfil_nome' => 'desc',
    ]]);

    $query = Pipeline::make()
        ->send(Usuario::query())
        ->through([JoinAll::class, Order::class])
        ->thenReturn();

    assertMatchesSnapshot([$query->toSql(), $query->getBindings()]);
});
