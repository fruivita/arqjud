<?php

/**
 * @see https://pestphp.com/docs/
 */

use App\Http\Controllers\Atendimento\GuiaController;
use App\Models\Guia;
use App\Models\Permissao;
use Database\Seeders\PerfilSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use function Pest\Laravel\get;

beforeEach(function () {
    $this->seed([PerfilSeeder::class]);

    $this->guia = Guia::factory()->create();

    login();
});

afterEach(fn () => logout());

// Autorização
test('usuário sem permissão não consegue gerar a guia em PDF', function () {
    get(route('atendimento.guia.pdf', ['guia' => $this->guia]))->assertForbidden();
});

// Caminho feliz
test('action index compartilha os dados esperados com a view/componente correto', function (bool $permissao) {
    Guia::factory(2)->create();

    concederPermissao(Permissao::GUIA_VIEW_ANY);

    get(route('atendimento.guia.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/Guia/Index')
                ->has('guias.data', 3) // 2 + 1
                ->has('guias.meta.termo')
                ->has('guias.meta.order')
        );
})->with([
    false,
    true,
]);

test('action show compartilha os dados esperados com a view/componente correto', function () {
    concederPermissao(Permissao::GUIA_VIEW);

    get(route('atendimento.guia.show', $this->guia))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Atendimento/Guia/Show')
                ->where('guia.data', guiaApi($this->guia) + ['links' => [
                    'view' => route('atendimento.guia.show', $this->guia),
                    'pdf' => route('atendimento.guia.pdf', $this->guia),
                ]])
        );
});

test('usuário com permissão consegue gerar a guia em PDF', function () {
    concederPermissao(Permissao::GUIA_VIEW);

    get(route('atendimento.guia.pdf', ['guia' => $this->guia]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename="guia.pdf"');
});

test('GuiaController usa trait', function () {
    expect(
        collect(class_uses(GuiaController::class))
            ->has([
                \App\Http\Traits\ComPaginacaoEmCache::class,
            ])
    )->toBeTrue();
});
