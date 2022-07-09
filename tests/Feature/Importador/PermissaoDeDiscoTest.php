<?php

/**
 * @see https://pestphp.com/docs/
 */
test('aquivo corporativo pode ser lido', function () {
    $arquivo = config('orgao.arquivo_corporativo');

    expect((new \SplFileInfo($arquivo))->isReadable())->toBeTrue();
})->group('integration');
