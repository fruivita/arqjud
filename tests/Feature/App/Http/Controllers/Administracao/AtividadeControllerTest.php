<?php

/**
 * @see https://pestphp.com/docs/
 * @see https://inertiajs.com/testing
 * @see https://hofmannsven.com/2020/testing-laravel-form-requests
 * @see https://github.com/jasonmccreary/laravel-test-assertions
 */

use App\Http\Controllers\Administracao\AtividadeController;
use App\Models\Permissao;
use App\Models\Usuario;
use Database\Seeders\PerfilSeeder;
use Illuminate\Support\Facades\Auth;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;


beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    activity()->disableLogging();
    Auth::login(Usuario::factory()->create());
    activity()->enableLogging();
});

afterEach(fn () => logout());

// Caminho feliz
test('action index compartilha os dados esperados com a view/componente correto', function () {
    activity('foo')->log('bar');
    activity('loren')->log('ipson');

    concederPermissao(Permissao::ATIVIDADE_VIEW_ANY);

    get(route('administracao.atividade.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Administracao/Atividade/Index')
                ->has('atividades.data', 2)
                ->has('atividades.meta.termo')
                ->has('atividades.meta.order')
        );
});

test('AtividadeController usa trait', function () {
    expect(
        collect(class_uses(AtividadeController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
                \App\Http\Traits\ComFeedback::class,
            ])
    )->toBeTrue();
});
