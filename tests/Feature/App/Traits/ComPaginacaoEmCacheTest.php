<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    login('foo');

    $this->classe = new class
    {
        use App\Traits\ComPaginacaoEmCache;
    };
});

afterEach(function () {
    logout();
});

// Inválidos/Inexistente
test('se a paginação informada e o cache forem inválidos, a paginação padrão será utilizada', function () {
    // 66 é uma opção inexistente
    Cache::put('foo', [
        'per_page' => ['/' => 66],
    ]);

    // 33 é uma opção inexistente
    expect($this->classe->perPage(33))->toBe($this->classe->paginacaoPadrao());
});

test('se a paginação informada for inválida, mas existir cache válido, o cache será utilizado', function () {
    Cache::put('foo', [
        'per_page' => ['/' => 50],
    ]);

    // 33 é uma opção inexistente
    expect($this->classe->perPage(33))->toBe(50);
});

// Caminho feliz
test('opções de paginação estão definidas', function () {
    expect($this->classe->getOpcoes())->toBe([10, 25, 50, 100]);
});

test('paginação padrão está definida', function () {
    expect($this->classe->paginacaoPadrao())->toBe(10);
});

test('sem informar paginação e sem cache, retorna a paginação padrão, isto é, o primeiro item do array de opções', function () {
    expect($this->classe->perPage())->toBe($this->classe->paginacaoPadrao());
});

test('sem informar paginação, mas com cache, retorna a paginação em cache', function () {
    Cache::put('foo', [
        'per_page' => ['/' => 50],
    ]);

    expect($this->classe->perPage())->toBe(50);
});

test('se a paginação for informada, ela será utilizada, mesmo que exista cache', function () {
    Cache::put('foo', [
        'per_page' => ['/' => 50],
    ]);

    expect($this->classe->perPage(25))->toBe(25);
});

test('paginação armazena em cache o valor utilizado', function () {
    Cache::put('foo', [
        'per_page' => ['/' => 50],
    ]);
    $this->classe->perPage(25);

    expect(Cache::get('foo'))->toBe([
        'per_page' => ['/' => 25],
    ]);
});

test('cache da paginação é individualizado para cada path', function () {
    Cache::put('foo', [
        'per_page' => ['/pathqualquer' => 50],
    ]);
    $this->classe->perPage();

    expect(Cache::get('foo'))->toBe([
        'per_page' => [
            '/pathqualquer' => 50,
            '/' => 10,
        ],
    ]);
});
