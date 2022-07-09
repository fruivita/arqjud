<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Rules\ArquivoExiste;
use Illuminate\Support\Facades\Storage;

test('valida se o arquivo existe no storage', function () {
    $disco = Storage::fake('disco-foo');

    $rule = new ArquivoExiste('disco-foo');

    expect($rule->passes('file', 'foo.txt'))->toBeFalse();

    $disco->put('foo.txt', 'loren ipsun dolor');

    expect($rule->passes('file', 'foo.txt'))->toBeTrue();

    $disco = Storage::fake('disco-foo');
});
