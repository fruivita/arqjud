<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Jobs\ImportarDadosRH;
use App\Models\Cargo;
use App\Models\FuncaoConfianca;
use App\Models\Lotacao;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;

// Caminho feliz
test('job ImportarDadosRH cria o log de atividade', function () {
    Auth::login(Usuario::factory()->create(['matricula' => '10000']));

    ImportarDadosRH::dispatch();

    $this
        ->assertDatabaseHas('activity_log', [
            'log_name' => __('Importação RH'),
            'event' => 'created',
            'matricula' => '10000',
            'description' => __('solicitada'),
        ]);
});

test('job ImportarDadosRH importa os dados do arquivo corporativo', function () {
    ImportarDadosRH::dispatchSync();

    expect(Cargo::count())->toBe(3)
        ->and(FuncaoConfianca::count())->toBe(3)
        ->and(Lotacao::count())->toBe(5)
        ->and(Usuario::count())->toBe(5);
});
