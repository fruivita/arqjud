<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://laravel.com/docs/9.x/console-tests
 */

use App\Enums\Queue as EQueue;
use App\Jobs\ImportarProcesso;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

// Falhas
test('comando processo:importar falha como esperado se o arquivo informado não existir', function () {
    $this->artisan('processo:importar')
        ->expectsQuestion(__('Nome do arquivo para importação? (Apenas arquivos CSV são aceitos)'), 'foo.csv')
        ->expectsOutput(__('Arquivo não encontrado'))
        ->assertFailed();
});

test('comando processo:importar falha como esperado se o arquivo informado não for um CSV', function () {
    $arquivo = 'dumb_file.txt';

    Storage::fake('processo', [
        'driver' => 'local',
    ])->put($arquivo, 'loren ipsum');

    $this->artisan('processo:importar')
        ->expectsQuestion('Nome do arquivo para importação? (Apenas arquivos CSV são aceitos)', $arquivo)
        ->expectsOutput('O arquivo foi encontrado, porém não é um arquivo validamente CSV')
        ->assertFailed();
});

// Happy path
test('comando processo:importar quando executado com sucessso envia o job para a queue', function () {
    Storage::fake('processo', ['driver' => 'local']);

    $arquivo = 'dumb_processos.csv';
    criarArquivoProcesso($arquivo, 'sem_campo_opcional');

    Queue::fake();

    $this
        ->artisan('processo:importar')
        ->expectsQuestion('Nome do arquivo para importação? (Apenas arquivos CSV são aceitos)', $arquivo)
        ->expectsOutput('Arquivo posto na fila de processamento')
        ->assertSuccessful();

    Queue::assertPushedOn(EQueue::Baixa->value, ImportarProcesso::class);
});

test('comando processo:importar quando executado com sucessso dispara o job ImportarProcesso', function () {
    Storage::fake('processo', ['driver' => 'local']);

    $arquivo = 'dumb_processos.csv';
    criarArquivoProcesso($arquivo, 'sem_campo_opcional');

    Bus::fake();

    $this
        ->artisan('processo:importar')
        ->expectsQuestion('Nome do arquivo para importação? (Apenas arquivos CSV são aceitos)', $arquivo)
        ->expectsOutput('Arquivo posto na fila de processamento')
        ->assertSuccessful();

    Bus::assertDispatched(ImportarProcesso::class, fn (ImportarProcesso $job) => $job->arquivo == $arquivo);
});
