<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Predio\PredioLivewireIndex;
use App\Models\Predio;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Livewire\Livewire;
use function Spatie\PestPluginTestTime\testTime;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->predio = Predio::factory()->create();

    login('foo');

    concederPermissao(Permissao::PredioViewAny->value);

    $this->chave = 'preferencias' . '-' . usuarioAutenticado()->username . '-' . 'PredioLivewireIndex';
});

afterEach(function () {
    logout();
});

// Validação
test('não aceita paginação fora das opções oferecidas', function () {
    Livewire::test(PredioLivewireIndex::class)
    ->set('preferencias.por_pagina', 33)
    ->call('salvarPreferencia')
    ->assertHasErrors(['preferencias.por_pagina' => 'in']);
});

test('não salva os valores em cache, caso a validação falhe', function () {
    expect(cache()->missing($this->chave))->toBeTrue();

    Livewire::test(PredioLivewireIndex::class)
    ->set('preferencias.por_pagina', 33) // valores possíveis: 10/25/50/100
    ->call('salvarPreferencia')
    ->assertHasErrors(['preferencias.por_pagina' => 'in']);

    expect(cache()->missing($this->chave))->toBeTrue();
});

// Falhas
test('salva as preferências em cache pelo prazo padrão se a quantidade de meses de armazenamento informada for menor ou igual a zero', function () {
    $componente = Livewire::test(PredioLivewireIndex::class);

    expect(cache()->missing($this->chave))->toBeTrue();

    testTime()->freeze();
    $componente->call('salvarPreferencia', 0);
    testTime()->addMonths(12);

    // cache ainda exite após 12 meses
    expect(cache()->has($this->chave))->toBeTrue();

    // expira cache
    testTime()->addSeconds(1);
    expect(cache()->missing($this->chave))->toBeTrue();
});

// Caminho feliz
test('armazena em cache as preferências que o usuário definiu', function () {
    $definidas_pelo_usuario = [
        'colunas' => ['predio', 'qtd_andares'],
        'por_pagina' => 50,
    ];

    Livewire::test(PredioLivewireIndex::class)
    ->set('preferencias', $definidas_pelo_usuario)
    ->call('salvarPreferencia')
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($definidas_pelo_usuario);
});

test('ao carregar o componente, se houver não cache, as preferências padrão serão utilizadas', function () {
    $preferencias_padrao = [
        'colunas' => ['predio', 'qtd_andares', 'localidade', 'acoes'],
        'por_pagina' => 10,
    ];

    Livewire::test(PredioLivewireIndex::class)
    ->assertSet('preferencias', $preferencias_padrao)
    ->assertHasNoErrors()
    ->assertOk();
});

test('ao carregar o componente, se houver cache, ele será utilizado para definir as preferências', function () {
    testTime()->freeze();

    $preferencias_em_cache = [
        'colunas' => ['localidade', 'acoes'],
        'por_pagina' => 50,
    ];
    cache()->put($this->chave, $preferencias_em_cache, now()->addMonths(12));

    Livewire::test(PredioLivewireIndex::class)
    ->assertSet('preferencias', $preferencias_em_cache)
    ->assertHasNoErrors()
    ->assertOk();

    expect(cache()->get($this->chave))->toBe($preferencias_em_cache);
});

test('o cache é válido, por padrão, por 12 meses', function () {
    $componente = Livewire::test(PredioLivewireIndex::class);

    expect(cache()->missing($this->chave))->toBeTrue();

    testTime()->freeze();
    $componente->call('salvarPreferencia');
    testTime()->addMonths(12);

    // cache ainda exite após 12 meses
    expect(cache()->has($this->chave))->toBeTrue();

    // expira cache
    testTime()->addSeconds(1);
    expect(cache()->missing($this->chave))->toBeTrue();
});

test('salva as preferências em cache pelo prazo informado', function () {
    $componente = Livewire::test(PredioLivewireIndex::class);

    expect(cache()->missing($this->chave))->toBeTrue();

    testTime()->freeze();
    $componente->call('salvarPreferencia', 6);
    testTime()->addMonths(6);

    // cache ainda exite após 6 meses
    expect(cache()->has($this->chave))->toBeTrue();

    // expira cache
    testTime()->addSeconds(1);
    expect(cache()->missing($this->chave))->toBeTrue();
});
