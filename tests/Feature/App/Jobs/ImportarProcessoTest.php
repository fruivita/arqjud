<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Jobs\ImportarProcesso;
use Illuminate\Support\Facades\Storage;

// Caminho feliz
test('job ImportarProcesso importa os processos', function () {
    Storage::fake('processo', ['driver' => 'local']);

    $arquivo = 'dumb_processos.csv';
    criarArquivoProcesso($arquivo, 'sem_campo_opcional');

    ImportarProcesso::dispatchSync($arquivo);

    $this->assertDatabaseCount('localidades', 2)
        ->assertDatabaseCount('predios', 1)
        ->assertDatabaseCount('andares', 1)
        ->assertDatabaseCount('salas', 1)
        ->assertDatabaseCount('estantes', 1)
        ->assertDatabaseCount('prateleiras', 1)
        ->assertDatabaseCount('caixas', 1)
        ->assertDatabaseCount('processos', 1);
});
