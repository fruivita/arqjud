<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Processo;
use App\Models\Sala;
use App\Models\VolumeCaixa;
use App\Services\Importador\ImportadorArquivoProcesso;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use function Spatie\Snapshots\assertMatchesFileSnapshot;

beforeEach(function () {
    $storage = Storage::fake('processo', ['driver' => 'local']);

    $this->nome_arquivo = 'dumb_processos.csv';
    $this->path_falha = $storage->path("erro-{$this->nome_arquivo}");
});

// Validação
test('registra os erros de validação em arquivo próprio respeitando o snapshot', function (string $stub) {
    criarArquivoProcesso($this->nome_arquivo, $stub);

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    assertMatchesFileSnapshot($this->path_falha);
})->with([
    'com_localidade_invalida',
    'com_predio_invalido',
    'com_andar_invalido',
    'com_sala_invalida',
    'com_estante_invalida',
    'com_prateleira_invalida',
    'com_caixa_invalida',
    'com_volume_caixa_invalido',
    'com_processo_invalido',
    'com_processo_pai_invalido',
    'com_localidade_criadora_invalida',
]);

test('data futura para o arquivamento é inválida', function () {
    criarArquivoProcesso($this->nome_arquivo, 'com_processo_arquivado_em_data_futura');

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    $this->assertDatabaseCount('processos', 0);
});

test('data futura para ano da caixa é inválida', function () {
    criarArquivoProcesso($this->nome_arquivo, 'com_caixa_de_ano_futuro');

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    $this->assertDatabaseCount('caixas', 0);
});

// Caminho feliz
test('make retorna o objeto da classe', function () {
    expect(ImportadorArquivoProcesso::make())->toBeInstanceOf(ImportadorArquivoProcesso::class);
});

test('cria o log de registro do inicio e final do processo de importação', function () {
    criarArquivoProcesso($this->nome_arquivo, 'sem_campo_opcional');

    Log::spy();
    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    Log::shouldHaveReceived('log')
        ->withArgs(fn ($level, $message) => $level === 'notice' && $message === __('Inicio da importação dos processos'))
        ->once();

    Log::shouldHaveReceived('log')
        ->withArgs(fn ($level, $message) => $level === 'notice' && $message === __('Final da importação dos processos'))
        ->once();
});

test('importação persiste todos os dados obrigatórios', function () {
    criarArquivoProcesso($this->nome_arquivo, 'sem_campo_opcional');

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    $this->assertDatabaseCount('localidades', 2)
        ->assertDatabaseHas('localidades', [
            'nome' => 'Yokohama',
        ])
        ->assertDatabaseHas('localidades', [
            'nome' => 'Madrid',
        ])
        ->assertDatabaseCount('predios', 1)
        ->assertDatabaseHas('predios', [
            'nome' => 'Empire State',
        ])
        ->assertDatabaseCount('andares', 1)
        ->assertDatabaseHas('andares', [
            'numero' => 10,
        ])
        ->assertDatabaseCount('salas', 1)
        ->assertDatabaseHas('salas', [
            'numero' => '100-s',
        ])
        ->assertDatabaseCount('estantes', 1)
        ->assertDatabaseHas('estantes', [
            'numero' => '0',
        ])
        ->assertDatabaseCount('prateleiras', 1)
        ->assertDatabaseHas('prateleiras', [
            'numero' => '0',
        ])
        ->assertDatabaseCount('caixas', 1)
        ->assertDatabaseHas('caixas', [
            'numero' => 5,
            'ano' => 2020,
            'guarda_permanente' => 0,
        ])
        ->assertDatabaseCount('volumes_caixa', 1)
        ->assertDatabaseHas('volumes_caixa', [
            'numero' => 3,
        ])
        ->assertDatabaseCount('processos', 1)
        ->assertDatabaseHas('processos', [
            'numero' => '26899909319841005657',
            'qtd_volumes' => 2,
            'guarda_permanente' => 0,
            'arquivado_em' => '2020-12-21',
        ]);
});

test('importação persiste todos os dados opcionais', function () {
    criarArquivoProcesso($this->nome_arquivo, 'com_campo_opcional');

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    $this->assertDatabaseCount('localidades', 2)
        ->assertDatabaseHas('localidades', [
            'nome' => 'Yokohama',
        ])
        ->assertDatabaseHas('localidades', [
            'nome' => 'Madrid',
        ])
        ->assertDatabaseCount('predios', 1)
        ->assertDatabaseHas('predios', [
            'nome' => 'Empire State',
        ])
        ->assertDatabaseCount('andares', 1)
        ->assertDatabaseHas('andares', [
            'numero' => 10,
        ])
        ->assertDatabaseCount('salas', 1)
        ->assertDatabaseHas('salas', [
            'numero' => '100-s',
        ])
        ->assertDatabaseCount('estantes', 1)
        ->assertDatabaseHas('estantes', [
            'numero' => '20-e',
        ])
        ->assertDatabaseCount('prateleiras', 1)
        ->assertDatabaseHas('prateleiras', [
            'numero' => '30-p',
        ])
        ->assertDatabaseCount('caixas', 1)
        ->assertDatabaseHas('caixas', [
            'numero' => 5,
            'ano' => 2020,
            'guarda_permanente' => 1,
            'complemento' => 'foo',
            'descricao' => 'Loren ipsum',
        ])
        ->assertDatabaseCount('volumes_caixa', 1)
        ->assertDatabaseHas('volumes_caixa', [
            'numero' => 3,
        ])
        ->assertDatabaseCount('processos', 1)
        ->assertDatabaseHas('processos', [
            'numero' => '26899909319841005657',
            'numero_antigo' => '0944643060',
            'qtd_volumes' => 2,
            'guarda_permanente' => 1,
            'arquivado_em' => '2020-12-21',
        ]);
});

test('cria os relacionamentos na importação', function () {
    criarArquivoProcesso($this->nome_arquivo, 'sem_campo_opcional');

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    $processo = Processo::with([
        'volumeCaixa.caixa.prateleira.estante.sala.andar.predio.localidade',
        'volumeCaixa.caixa.localidadeCriadora',
    ])->firstWhere('processos.numero', '26899909319841005657');

    expect($processo->volumeCaixa->numero)->toBe(3)
        ->and($processo->volumeCaixa->caixa->numero)->toBe(5)
        ->and($processo->volumeCaixa->caixa->prateleira->numero)->toBe('0')
        ->and($processo->volumeCaixa->caixa->prateleira->estante->numero)->toBe('0')
        ->and($processo->volumeCaixa->caixa->prateleira->estante->sala->numero)->toBe('100-s')
        ->and($processo->volumeCaixa->caixa->prateleira->estante->sala->andar->numero)->toBe(10)
        ->and($processo->volumeCaixa->caixa->prateleira->estante->sala->andar->predio->nome)->toBe('Empire State')
        ->and($processo->volumeCaixa->caixa->prateleira->estante->sala->andar->predio->localidade->nome)->toBe('Madrid')
        ->and($processo->volumeCaixa->caixa->localidadeCriadora->nome)->toBe('Yokohama');
});

test('cria o relacionamento com o processo pai na importação', function () {
    criarArquivoProcesso($this->nome_arquivo, 'com_processo_pai');

    ImportadorArquivoProcesso::make()->importar($this->nome_arquivo);

    $processo = Processo::with('processoPai')->firstWhere('numero', '26899909319841005657');

    expect($processo->numero)->toBe('2689990-93.1984.1.00.5657')
        ->and($processo->processoPai->numero)->toBe('8414518-72.1934.4.03.2589');
});
