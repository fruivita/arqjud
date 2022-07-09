<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Models\Configuracao;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

// Exceptions
test('lança exception ao tentar criar configuração com campo inválido', function ($campo, $valor, $mensagem) {
    expect(
        fn () => Configuracao::factory()->create([$campo => $valor])
    )->toThrow(QueryException::class, $mensagem);
})->with([
    ['superadmin', Str::random(21), 'Data too long for column'], // máximo 20 caracteres
    ['superadmin', null,            'cannot be null'],           // obrigatório
]);

// Caminho feliz
test('aceita campos em seus tamanhos máximos', function () {
    Configuracao::factory()->create(['superadmin' => Str::random(20)]);

    expect(Configuracao::count())->toBe(1);
});

test('id das configurações da aplicação está definido', function () {
    expect(Configuracao::ID)->toBe(101);
});
