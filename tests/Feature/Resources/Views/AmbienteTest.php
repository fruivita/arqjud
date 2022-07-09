<?php

/**
 * @see https://pestphp.com/docs/
 */

use Illuminate\Support\Facades\App;
use function Pest\Laravel\get;

test('tarja de ambiente é mostrada se a aplicação não estiver em produção', function () {
    get(route('login'))
    ->assertSee(__(str()->ucfirst(App::environment())));
});

test('tarja de ambiente não é exibida se a aplicação estiver em produção', function () {
    App::shouldReceive('environment')
    ->andReturn('production');

    get(route('login'))
    ->assertDontSee(__(str()->ucfirst(App::environment())));
});
