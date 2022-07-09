<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Perfil;
use App\Models\Permissao;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar permissões duplicadas, isto é, com mesmo id ou nome', function () {
    expect(
        fn () => Permissao::factory(2)->create(['id' => 1])
    )->toThrow(QueryException::class, 'Duplicate entry');

    expect(
        fn () => Permissao::factory(2)->create(['nome' => 'foo'])
    )->toThrow(QueryException::class, 'Duplicate entry');
});

test('lança exception ao tentar criar permissão com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Permissao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['nome', Str::random(51),       'Data too long for column'], // máximo 50 caracteres
    ['nome', null,                  'cannot be null'],           // obrigatório
    ['descricao', Str::random(256), 'Data too long for column'], // máximo 255 caracteres
]);

// Falhas
test('método salvaESincronizaPerfis faz rollback caso o update da permissão falhe', function () {
    $nome = 'foo';
    $descricao = 'bar';

    $permissao = Permissao::factory()->create([
        'nome' => $nome,
        'descricao' => $descricao,
    ]);

    $permissao->nome = 'new foo';
    $permissao->descricao = 'new bar';

    // tentativa de criar relacionamento com perfis inexistentes
    $salvo = $permissao->salvaESincronizaPerfis([10, 20]);

    $permissao->refresh()->load('perfis');

    expect($salvo)->toBeFalse()
    ->and($permissao->nome)->toBe($nome)
    ->and($permissao->descricao)->toBe($descricao)
    ->and($permissao->perfis)->toBeEmpty();
});

test('método salvaESincronizaPerfis registra em log a falha no update da permissão', function () {
    Log::spy();

    $permissao = Permissao::factory()->create();

    // tentativa de criar relacionamento com perfis inexistentes
    $permissao->salvaESincronizaPerfis([1, 2]);

    Log::shouldHaveReceived('error')
    ->withArgs(fn ($message) => $message === __('Falha na atualização da permissão'))
    ->once();
});

// Caminho feliz
test('cria muitas permissões', function () {
    Permissao::factory(30)->create();

    expect(Permissao::count())->toBe(30);
});

test('campos opcionais estão definidos', function () {
    Permissao::factory()->create(['descricao' => null]);

    expect(Permissao::count())->toBe(1);
});

test('aceita campos em seus tamanhos máximos', function () {
    Permissao::factory()->create([
        'nome' => Str::random(50),
        'descricao' => Str::random(255),
    ]);

    expect(Permissao::count())->toBe(1);
});

test('uma permissão pertente a muitos perfis', function () {
    $permissao = Permissao::factory()->has(Perfil::factory(3), 'perfis')->create();

    $permissao->load('perfis');

    expect($permissao->perfis)->toHaveCount(3);
});

test('método salvaESincronizaPerfis salva os novos atributos e cria os relacionamentos com os perfis informados', function () {
    $nome = 'foo';
    $descricao = 'bar';

    $permissao = Permissao::factory()->create([
        'nome' => 'baz',
        'descricao' => 'foo bar baz',
    ]);

    Perfil::factory()->create(['id' => 1]);
    Perfil::factory()->create(['id' => 2]);
    Perfil::factory()->create(['id' => 3]);

    $permissao->nome = $nome;
    $permissao->descricao = $descricao;

    $salvo = $permissao->salvaESincronizaPerfis([1, 3]);
    $permissao->refresh()->load('perfis');

    expect($salvo)->toBeTrue()
    ->and($permissao->nome)->toBe($nome)
    ->and($permissao->descricao)->toBe($descricao)
    ->and($permissao->perfis->modelKeys())->toBe([1, 3]);
});

test('retorna as permissões ordenadas pelo escopo de ordenação padrão', function () {
    Permissao::factory()->create(['id' => 30]);
    Permissao::factory()->create(['id' => 10]);
    Permissao::factory()->create(['id' => 20]);

    $permissaos = Permissao::ordenacaoPadrao()->get();

    expect($permissaos->get(0)->id)->toBe(10)
    ->and($permissaos->get(1)->id)->toBe(20)
    ->and($permissaos->get(2)->id)->toBe(30);
});
