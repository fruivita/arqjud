<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Enums\Queue as EQueue;
use App\Jobs\ImportarDadosRH;
use App\Listeners\ImportacaoDadosRHEventSubscriber;
use App\Models\Cargo;
use App\Models\FuncaoConfianca;
use App\Models\Lotacao;
use App\Models\Perfil;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use function Spatie\PestPluginTestTime\testTime;

beforeAll(fn () => \Spatie\Once\Cache::getInstance()->disable());

// Caminho feliz
test('registra o log de início e fim da importação', function () {
    testTime()->freeze();

    Log::spy();

    ImportarDadosRH::dispatchSync();

    Log::shouldHaveReceived('notice')
        ->withArgs(function ($message, $data) {
            return $message === __('Importação dos dados corporativos iniciada')
                && $data === [
                    'iniciado_em' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                    'arquivo' => 'tests/template/Corporativo.xml',
                ];
        })->once();

    Log::shouldHaveReceived('notice')
        ->withArgs(function ($message, $data) {
            return $message === __('Importação dos dados corporativos concluída')
                && $data === [
                    'concluido_em' => now()->tz(config('app.tz'))->format('d-m-Y H:i:s'),
                    'arquivo' => 'tests/template/Corporativo.xml',
                ];
        })->once();
});

test('reseta o perfil do usuário ao mudar seu cargo', function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()->for(Cargo::factory(['id' => 1000]), 'cargo')->create(['matricula' => 'ES44444']);

    expect($usuario->perfil_id)->not->toBe(Perfil::padrao()->id);

    ImportarDadosRH::dispatchSync();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe(Perfil::padrao()->id)
        ->and($usuario->cargo_id)->not->toBe(1000);
});

test('reseta o perfil do usuário ao mudar sua lotação', function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()->for(Lotacao::factory(['id' => 1000]), 'lotacao')->create(['matricula' => 'ES44444']);

    expect($usuario->perfil_id)->not->toBe(Perfil::padrao()->id);

    ImportarDadosRH::dispatchSync();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe(Perfil::padrao()->id)
        ->and($usuario->lotacao_id)->not->toBe(1000);
});

test('reseta o perfil do usuário ao mudar sua função de confiança', function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()->for(FuncaoConfianca::factory(['id' => 1000]), 'funcaoConfianca')->create(['matricula' => 'ES44444']);

    expect($usuario->perfil_id)->not->toBe(Perfil::padrao()->id);

    ImportarDadosRH::dispatchSync();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe(Perfil::padrao()->id)
        ->and($usuario->funcao_confianca_id)->toBeEmpty();
});

test('usuário com perfil administrador não é resetado, mesmo havendo mudança de cargo, lotação ou função', function () {
    $this->seed([PerfilSeeder::class]);

    $usuario = Usuario::factory()
        ->for(Cargo::factory(['id' => 1000]), 'cargo')
        ->for(Lotacao::factory(['id' => 1000]), 'lotacao')
        ->for(FuncaoConfianca::factory(['id' => 1000]), 'funcaoConfianca')
        ->for(Perfil::administrador(), 'perfil')
        ->create(['matricula' => 'ES44444']);

    expect($usuario->perfil_id)->toBe(Perfil::administrador()->id);

    ImportarDadosRH::dispatchSync();

    $usuario->refresh();

    expect($usuario->perfil_id)->toBe(Perfil::administrador()->id)
        ->and($usuario->cargo_id)->not->toBe(1000)
        ->and($usuario->lotacao_id)->not->toBe(1000)
        ->and($usuario->funcao_confianca_id)->toBeEmpty();
});

test('envia a tratativa dos eventos para a queue', function () {
    $this->seed([PerfilSeeder::class]);

    Queue::fake()->except([
        ImportarDadosRH::class,
    ]);

    Usuario::factory()
        ->for(Cargo::factory(['id' => 1000]), 'cargo')
        ->for(Lotacao::factory(['id' => 1000]), 'lotacao')
        ->for(FuncaoConfianca::factory(['id' => 1000]), 'funcaoConfianca')
        ->for(Perfil::administrador(), 'perfil')
        ->create(['matricula' => 'ES44444']);

    ImportarDadosRH::dispatchSync();

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        CallQueuedListener::class,
        fn (CallQueuedListener $listener) => $listener->class === ImportacaoDadosRHEventSubscriber::class && $listener->method === 'handleImportacaoIniciada'
    );

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        CallQueuedListener::class,
        fn (CallQueuedListener $listener) => $listener->class === ImportacaoDadosRHEventSubscriber::class && $listener->method === 'handleImportacaoConcluida'
    );

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        CallQueuedListener::class,
        fn (CallQueuedListener $listener) => $listener->class === ImportacaoDadosRHEventSubscriber::class && $listener->method === 'handleCargoUsuarioAlterado'
    );

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        CallQueuedListener::class,
        fn (CallQueuedListener $listener) => $listener->class === ImportacaoDadosRHEventSubscriber::class && $listener->method === 'handleFuncaoConfiancaUsuarioAlterada'
    );

    Queue::assertPushedOn(
        EQueue::Baixa->value,
        CallQueuedListener::class,
        fn (CallQueuedListener $listener) => $listener->class === ImportacaoDadosRHEventSubscriber::class && $listener->method === 'handleLotacaoUsuarioAlterada'
    );
});
