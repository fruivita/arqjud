<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Enums\Permissao;
use App\Http\Livewire\Arquivamento\Cadastro\Andar\AndarLivewireUpdate;
use App\Models\Andar;
use Database\Seeders\LotacaoSeeder;
use Database\Seeders\PerfilSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([LotacaoSeeder::class, PerfilSeeder::class]);

    $this->andar = Andar::factory()->create(['numero' => 20]);

    login('foo');
});

afterEach(function () {
    logout();
});

// Caminho feliz
test('converte para null strings vazias, inclusive com diversos espaços em branco, se for aplicável', function ($string, $esperado) {
    concederPermissao(Permissao::AndarUpdate->value);

    Livewire::test(AndarLivewireUpdate::class, ['id' => $this->andar->id])
    ->set('modo_edicao', true)
    ->set('andar.apelido', $string)
    ->call('update')
    ->assertHasNoErrors()
    ->assertOk();

    $this->andar->refresh();

    expect($this->andar->apelido)->toBe($esperado);
})->with([
    [''     , null],  // vazio
    ['     ', null],  // vazio pois haverá trim
    ['20º'  , '20º'], // não conversível, pois um valor válido
])->skip();
